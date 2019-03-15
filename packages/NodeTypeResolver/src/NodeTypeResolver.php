<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Countable;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

    /**
     * @var TypeToStringResolver
     */
    private $typeToStringResolver;

    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var StaticTypeToStringResolver
     */
    private $staticTypeToStringResolver;

    /**
     * @param PerNodeTypeResolverInterface[] $perNodeTypeResolvers
     */
    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        NameResolver $nameResolver,
        ClassManipulator $classManipulator,
        StaticTypeToStringResolver $staticTypeToStringResolver,
        TypeToStringResolver $typeToStringResolver,
        Broker $broker,
        ClassReflectionTypesResolver $classReflectionTypesResolver,
        array $perNodeTypeResolvers
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nameResolver = $nameResolver;
        $this->classManipulator = $classManipulator;
        $this->staticTypeToStringResolver = $staticTypeToStringResolver;
        $this->typeToStringResolver = $typeToStringResolver;
        $this->broker = $broker;
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;

        foreach ($perNodeTypeResolvers as $perNodeTypeResolver) {
            $this->addPerNodeTypeResolver($perNodeTypeResolver);
        }
    }

    public function isType(Node $node, string $type): bool
    {
        $resolvedNodeTypes = $this->getTypes($node);

        // fnmatch support
        if (Strings::contains($type, '*')) {
            foreach ($resolvedNodeTypes as $nodeType) {
                if (fnmatch($type, $nodeType, FNM_NOESCAPE)) {
                    return true;
                }
            }

            return false;
        }

        return in_array($type, $resolvedNodeTypes, true);
    }

    /**
     * @return string[]
     */
    public function getTypes(Node $node): array
    {
        // @todo should be resolved by NodeTypeResolver internally
        if ($node instanceof MethodCall || $node instanceof PropertyFetch || $node instanceof ArrayDimFetch) {
            return $this->resolve($node->var);
        }

        return $this->resolve($node);
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        if ($node instanceof ClassMethod || $node instanceof ClassConst) {
            return $this->resolveClassNode($node);
        }

        if ($node instanceof StaticCall) {
            return $this->resolveStaticCall($node);
        }

        if ($node instanceof ClassConstFetch) {
            return $this->resolve($node->class);
        }

        if ($node instanceof Cast) {
            return $this->resolve($node->expr);
        }

        $types = $this->resolveFirstTypes($node);
        if ($types === []) {
            return $types;
        }

        // complete parent types - parent classes, interfaces and traits
        foreach ($types as $i => $type) {
            // remove scalar types and other non-existing ones
            if ($type === 'null' || $type === null) {
                unset($types[$i]);
                continue;
            }

            if (class_exists($type)) {
                $types += $this->classReflectionTypesResolver->resolve($this->broker->getClass($type));
            }
        }

        return $types;
    }

    public function isStringType(Node $node): bool
    {
        return $this->getNodeStaticType($node) instanceof StringType;
    }

    public function isIntType(Node $node): bool
    {
        return $this->getNodeStaticType($node) instanceof IntegerType;
    }

    public function isFloatType(Node $node): bool
    {
        return $this->getNodeStaticType($node) instanceof FloatType;
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

                if (count($itemTypes) > 0) {
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

    public function isNullableObjectType(Node $node): bool
    {
        $nodeType = $this->getNodeStaticType($node);
        if (! $nodeType instanceof UnionType) {
            return false;
        }

        if ($nodeType->isSuperTypeOf(new NullType())->no()) {
            return false;
        }

        if (count($nodeType->getTypes()) !== 2) {
            return false;
        }

        foreach ($nodeType->getTypes() as $subtype) {
            if ($subtype instanceof ObjectType) {
                return true;
            }
        }

        return false;
    }

    private function addPerNodeTypeResolver(PerNodeTypeResolverInterface $perNodeTypeResolver): void
    {
        foreach ($perNodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->perNodeTypeResolvers[$nodeClass] = $perNodeTypeResolver;
        }

        // in-code setter injection to drop CompilerPass requirement for 3rd party package install
        if ($perNodeTypeResolver instanceof NodeTypeResolverAwareInterface) {
            $perNodeTypeResolver->setNodeTypeResolver($this);
        }
    }

    /**
     * @param ClassConst|ClassMethod $node
     * @return string[]
     */
    private function resolveClassNode(Node $node): array
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            throw new ShouldNotHappenException();
        }

        return $this->resolve($classNode);
    }

    /**
     * @return string[]
     */
    private function resolveFirstTypes(Node $node): array
    {
        // nodes that cannot be resolver by PHPStan
        $nodeClass = get_class($node);

        if (isset($this->perNodeTypeResolvers[$nodeClass])) {
            return $this->perNodeTypeResolvers[$nodeClass]->resolve($node);
        }

        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        if ($nodeScope === null) {
            return [];
        }

        if (! $node instanceof Expr) {
            return [];
        }

        // PHPStan
        $type = $nodeScope->getType($node);

        $typesInStrings = $this->typeToStringResolver->resolve($type);

        // hot fix for phpstan not resolving chain method calls
        if ($node instanceof MethodCall && ! $typesInStrings) {
            return $this->resolveFirstTypes($node->var);
        }

        return $typesInStrings;
    }

    /**
     * @return string[]
     */
    private function resolveStaticCall(StaticCall $staticCall): array
    {
        $classTypes = $this->resolve($staticCall->class);
        $methodName = $this->nameResolver->resolve($staticCall->name);

        // no specific method found, return class types, e.g. <ClassType>::$method()
        if (! is_string($methodName)) {
            return $classTypes;
        }

        foreach ($classTypes as $classType) {
            if (! method_exists($classType, $methodName)) {
                continue;
            }

            /** @var Scope|null $nodeScope */
            $nodeScope = $staticCall->getAttribute(Attribute::SCOPE);
            if ($nodeScope === null) {
                return $classTypes;
            }

            $type = $nodeScope->getType($staticCall);
            if ($type instanceof ObjectType) {
                return [$type->getClassName()];
            }
        }

        return $classTypes;
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
            if ($intersectionNodeType instanceof ArrayType || $intersectionNodeType instanceof HasOffsetType || $intersectionNodeType instanceof NonEmptyArrayType) {
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

        $propertyPropertyNode = $this->classManipulator->getProperty($classNode, $propertyName);

        if ($propertyPropertyNode === null) {
            return false;
        }

        return $propertyPropertyNode->default instanceof Array_;
    }
}
