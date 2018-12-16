<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Countable;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Node\Attribute;
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

    public function __construct(BetterStandardPrinter $betterStandardPrinter, NameResolver $nameResolver)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nameResolver = $nameResolver;
    }

    public function isStringType(Node $node): bool
    {
        return $this->getNodeType($node) instanceof StringType;
    }

    public function isStringyType(Node $node): bool
    {
        $nodeType = $this->getNodeType($node);
        if ($nodeType instanceof StringType || $nodeType instanceof ConstantStringType) {
            return true;
        }

        if ($nodeType instanceof UnionType) {
            foreach ($nodeType->getTypes() as $singleType) {
                if (! $singleType instanceof StringType && ! $singleType instanceof ConstantStringType) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function isNullType(Node $node): bool
    {
        return $this->getNodeType($node) instanceof NullType;
    }

    /**
     * e.g. string|null, ObjectNull|null
     */
    public function isNullableType(Node $node): bool
    {
        $nodeType = $this->getNodeType($node);
        if (! $nodeType instanceof UnionType) {
            return false;
        }

        return $nodeType->isSuperTypeOf(new NullType())->yes();
    }

    public function isBoolType(Node $node): bool
    {
        return $this->getNodeType($node) instanceof BooleanType;
    }

    public function isCountableType(Node $node): bool
    {
        $nodeType = $this->getNodeType($node);
        if ($nodeType === null) {
            return false;
        }

        $nodeType = $this->correctPregMatchType($node, $nodeType);

        if ($nodeType instanceof ObjectType) {
            return is_a($nodeType->getClassName(), Countable::class, true);
        }

        if ($nodeType instanceof IntersectionType) {
            foreach ($nodeType->getTypes() as $intersectionNodeType) {
                if ($intersectionNodeType instanceof ArrayType || $intersectionNodeType instanceof HasOffsetType) {
                    continue;
                }

                return false;
            }

            return true;
        }

        return $nodeType instanceof ArrayType;
    }

    private function getNodeType(Node $node): ?Type
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        if (! $node instanceof Expr || $nodeScope === null) {
            return null;
        }

        return $nodeScope->getType($node);
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
        if ($this->betterStandardPrinter->prettyPrint(
            [$funcCallNode->args[2]->value]
        ) !== $this->betterStandardPrinter->prettyPrint([$node])) {
            return $originalType;
        }

        if ($originalType instanceof ArrayType) {
            return $originalType;
        }

        return new ArrayType(new MixedType(), new MixedType());
    }
}
