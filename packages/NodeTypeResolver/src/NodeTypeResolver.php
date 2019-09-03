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
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeToStringResolver as TypeToStringResolver;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

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
     * @var StaticTypeToStringResolver
     */
    private $staticTypeToStringResolver;

    /**
     * @var TypeToStringResolver
     */
    private $typeToStringResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @param PerNodeTypeResolverInterface[] $perNodeTypeResolvers
     */
    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        NameResolver $nameResolver,
        StaticTypeToStringResolver $staticTypeToStringResolver,
        TypeToStringResolver $typeToStringResolver,
        Broker $broker,
        ClassReflectionTypesResolver $classReflectionTypesResolver,
        CallableNodeTraverser $callableNodeTraverser,
        array $perNodeTypeResolvers
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nameResolver = $nameResolver;
        $this->staticTypeToStringResolver = $staticTypeToStringResolver;
        $this->typeToStringResolver = $typeToStringResolver;
        $this->broker = $broker;
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;

        foreach ($perNodeTypeResolvers as $perNodeTypeResolver) {
            $this->addPerNodeTypeResolver($perNodeTypeResolver);
        }
        $this->callableNodeTraverser = $callableNodeTraverser;
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
        if ($node instanceof MethodCall || $node instanceof ArrayDimFetch) {
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

    public function isStringyType(Node $node): bool
    {
        $nodeType = $this->getStaticType($node);
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

    /**
     * e.g. string|null, ObjectNull|null
     */
    public function isNullableType(Node $node): bool
    {
        $nodeType = $this->getStaticType($node);
        if (! $nodeType instanceof UnionType) {
            return false;
        }

        return $nodeType->isSuperTypeOf(new NullType())->yes();
    }

    public function isCountableType(Node $node): bool
    {
        $nodeType = $this->getStaticType($node);
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
        $nodeStaticType = $this->getStaticType($node);
        if ($nodeStaticType === null) {
            return false;
        }

        $nodeStaticType = $this->correctPregMatchType($node, $nodeStaticType);
        if ($this->isIntersectionArrayType($nodeStaticType)) {
            return true;
        }

        if ($node instanceof PropertyFetch) {
            // PHPStan false positive, when variable has type[] docblock, but default array is missing
            if ($this->isPropertyFetchWithArrayDefault($node) === false) {
                return false;
            }
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

    public function getStaticType(Node $node): ?Type
    {
        if ($node instanceof String_) {
            return new ConstantStringType($node->value);
        }

        if ($node instanceof Param) {
            $paramStaticType = $this->resolveParamStaticType($node);
            if ($paramStaticType !== null) {
                return $paramStaticType;
            }
        }

        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $node instanceof Expr || $nodeScope === null) {
            return null;
        }

        if ($node instanceof New_) {
            if ($this->isAnonymousClass($node->class)) {
                return new ObjectWithoutClassType();
            }
        }

        return $nodeScope->getType($node);
    }

    /**
     * @return string[]
     */
    public function resolveSingleTypeToStrings(Node $node): array
    {
        if ($this->isArrayType($node)) {
            $arrayType = $this->getStaticType($node);
            if ($arrayType instanceof ArrayType) {
                $itemTypes = $this->staticTypeToStringResolver->resolveObjectType($arrayType->getItemType());

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

        $nodeStaticType = $this->getStaticType($node);

        return $this->staticTypeToStringResolver->resolveObjectType($nodeStaticType);
    }

    public function isNullableObjectType(Node $node): bool
    {
        $nodeType = $this->getStaticType($node);
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

    public function isNumberType(Node $node): bool
    {
        return $this->isStaticType($node, IntegerType::class) || $this->isStaticType($node, FloatType::class);
    }

    public function isStaticType(Node $node, string $staticTypeClass): bool
    {
        if (! is_a($staticTypeClass, Type::class, true)) {
            throw new ShouldNotHappenException(sprintf(
                '"%s" in "%s()" must be type of "%s"',
                $staticTypeClass,
                __METHOD__,
                Type::class
            ));
        }

        $nodeStaticType = $this->getStaticType($node);
        if ($nodeStaticType === null) {
            return false;
        }

        return is_a($nodeStaticType, $staticTypeClass);
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
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            // anonymous class
            return [];
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
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope === null) {
            return [];
        }

        if (! $node instanceof Expr) {
            return [];
        }

        // skip anonymous classes, ref https://github.com/rectorphp/rector/issues/1574
        if ($node instanceof New_) {
            if ($this->isAnonymousClass($node->class)) {
                return [];
            }
        }

        $type = $nodeScope->getType($node);

        $typesInStrings = $this->typeToStringResolver->resolveAnyType($type);

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
        $methodName = $this->nameResolver->getName($staticCall->name);

        // no specific method found, return class types, e.g. <ClassType>::$method()
        if (! is_string($methodName)) {
            return $classTypes;
        }

        foreach ($classTypes as $classType) {
            if (! method_exists($classType, $methodName)) {
                continue;
            }

            /** @var Scope|null $nodeScope */
            $nodeScope = $staticCall->getAttribute(AttributeKey::SCOPE);
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
        $previousExpression = $node->getAttribute(AttributeKey::CURRENT_EXPRESSION);
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
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);

        $propertyName = $this->nameResolver->getName($node->name);
        if ($propertyName === null) {
            return false;
        }

        $propertyPropertyNode = $this->getClassNodeProperty($classNode, $propertyName);
        if ($propertyPropertyNode === null) {
            return false;
        }

        return $propertyPropertyNode->default instanceof Array_;
    }

    private function getClassNodeProperty(Class_ $class, string $name): ?PropertyProperty
    {
        foreach ($class->getProperties() as $property) {
            foreach ($property->props as $propertyProperty) {
                if ($this->nameResolver->isName($propertyProperty, $name)) {
                    return $propertyProperty;
                }
            }
        }

        return null;
    }

    private function isAnonymousClass(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        $className = $this->nameResolver->getName($node);

        return $className === null || Strings::contains($className, 'AnonymousClass');
    }

    private function resolveParamStaticType(Node $node): ?Type
    {
        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethod === null) {
            return null;
        }

        /** @var string $paramName */
        $paramName = $this->nameResolver->getName($node);
        $paramStaticType = null;

        // special case for param inside method/function
        $this->callableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node) use ($paramName, &$paramStaticType): ?int {
                if (! $node instanceof Variable) {
                    return null;
                }

                if (! $this->nameResolver->isName($node, $paramName)) {
                    return null;
                }

                $paramStaticType = $this->getStaticType($node);

                return NodeTraverser::STOP_TRAVERSAL;
            }
        );

        return $paramStaticType;
    }
}
