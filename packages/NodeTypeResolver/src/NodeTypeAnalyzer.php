<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Countable;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class NodeTypeAnalyzer
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ClassMaintainer
     */
    private $classMaintainer;

    /**
     * @var StaticTypeToStringResolver
     */
    private $staticTypeToStringResolver;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        NameResolver $nameResolver,
        ClassMaintainer $classMaintainer,
        StaticTypeToStringResolver $staticTypeToStringResolver
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nameResolver = $nameResolver;
        $this->classMaintainer = $classMaintainer;
        $this->staticTypeToStringResolver = $staticTypeToStringResolver;
    }

    public function isStringType(Node $node): bool
    {
        return $this->getNodeStaticType($node) instanceof StringType;
    }

    public function isIntType(Node $node): bool
    {
        return $this->getNodeStaticType($node) instanceof IntegerType;
    }

    public function isStringyType(Node $node): bool
    {
        $nodeType = $this->getNodeStaticType($node);
        if ($nodeType instanceof StringType) {
            return true;
        }

        if ($nodeType instanceof UnionType) {
            foreach ($nodeType->getTypes() as $singleType) {
                if (! $singleType instanceof StringType) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function isNullType(Node $node): bool
    {
        return $this->getNodeStaticType($node) instanceof NullType;
    }

    /**
     * e.g. string|null, ObjectNull|null
     */
    public function isNullableType(Node $node): bool
    {
        $nodeType = $this->getNodeStaticType($node);
        if (! $nodeType instanceof UnionType) {
            return false;
        }

        return $nodeType->isSuperTypeOf(new NullType())->yes();
    }

    public function isBoolType(Node $node): bool
    {
        return $this->getNodeStaticType($node) instanceof BooleanType;
    }

    public function isCountableType(Node $node): bool
    {
        $nodeType = $this->getNodeStaticType($node);
        if ($nodeType === null) {
            return false;
        }

        $nodeType = $this->correctPregMatchType($node, $nodeType);
        if ($nodeType instanceof ObjectType) {
            return is_a($nodeType->getClassName(), Countable::class, true);
        }

        return $this->isArrayType($node);
    }

    public function isArrayType(Node $node): bool
    {
        $nodeStaticType = $this->getNodeStaticType($node);
        if ($nodeStaticType === null) {
            return false;
        }

        $nodeStaticType = $this->correctPregMatchType($node, $nodeStaticType);
        if ($this->isIntersectionArrayType($nodeStaticType)) {
            return true;
        }

        if ($nodeStaticType instanceof MixedType) {
            if ($nodeStaticType->isExplicitMixed()) {
                return false;
            }

            if ($this->isPropertyFetchWithArrayDefault($node)) {
                return true;
            }
        }

        return $nodeStaticType instanceof ArrayType;
    }

    public function getNodeStaticType(Node $node): ?Type
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        if (! $node instanceof Expr || $nodeScope === null) {
            return null;
        }

        return $nodeScope->getType($node);
    }

    /**
     * @return string[]
     */
    public function resolveSingleTypeToStrings(Node $node): array
    {
        if ($this->isArrayType($node)) {
            $arrayType = $this->getNodeStaticType($node);
            if ($arrayType instanceof ArrayType) {
                $itemTypes = $this->staticTypeToStringResolver->resolve($arrayType->getItemType());
                foreach ($itemTypes as $key => $itemType) {
                    $itemTypes[$key] = $itemType . '[]';
                }

                if (count($itemTypes)) {
                    return [implode('|', $itemTypes)];
                }
            }

            return ['array'];
        }

        if ($this->isStringyType($node)) {
            return ['string'];
        }

        $nodeStaticType = $this->getNodeStaticType($node);

        return $this->staticTypeToStringResolver->resolve($nodeStaticType);
    }

    /**
     * Special case for "preg_match(), preg_match_all()" - with 3rd argument
     * @covers https://github.com/rectorphp/rector/issues/786
     */
    private function correctPregMatchType(Node $node, Type $originalType): Type
    {
        /** @var Expression|null $previousExpression */
        $previousExpression = $node->getAttribute(Attribute::CURRENT_EXPRESSION);
        if ($previousExpression === null) {
            return $originalType;
        }

        if (! $previousExpression->expr instanceof FuncCall) {
            return $originalType;
        }

        $funcCallNode = $previousExpression->expr;
        if (! $this->nameResolver->isNames($funcCallNode, ['preg_match', 'preg_match_all'])) {
            return $originalType;
        }

        if (! isset($funcCallNode->args[2])) {
            return $originalType;
        }

        // are the same variables
        if (! $this->betterStandardPrinter->areNodesEqual($funcCallNode->args[2]->value, $node)) {
            return $originalType;
        }

        if ($originalType instanceof ArrayType) {
            return $originalType;
        }

        return new ArrayType(new MixedType(), new MixedType());
    }

    private function isIntersectionArrayType(Type $nodeType): bool
    {
        if (! $nodeType instanceof IntersectionType) {
            return false;
        }

        foreach ($nodeType->getTypes() as $intersectionNodeType) {
            if ($intersectionNodeType instanceof ArrayType || $intersectionNodeType instanceof HasOffsetType) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * phpstan bug workaround - https://phpstan.org/r/0443f283-244c-42b8-8373-85e7deb3504c
     */
    private function isPropertyFetchWithArrayDefault(Node $node): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        /** @var Class_ $classNode */
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);

        $propertyName = $this->nameResolver->resolve($node->name);
        if ($propertyName === null) {
            return false;
        }

        $propertyNode = $this->classMaintainer->getProperty($classNode, $propertyName);

        if ($propertyNode === null) {
            return false;
        }

        return $propertyNode->props[0]->default instanceof Array_;
    }
}
