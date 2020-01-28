<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Countable;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeCorrector\PregMatchTypeCorrector;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;
use ReflectionProperty;
use Symfony\Component\Finder\SplFileInfo;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var BetterPhpDocParser
     */
    private $betterPhpDocParser;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var PregMatchTypeCorrector
     */
    private $pregMatchTypeCorrector;

    /**
     * @param PerNodeTypeResolverInterface[] $perNodeTypeResolvers
     */
    public function __construct(
        BetterPhpDocParser $betterPhpDocParser,
        NameResolver $nameResolver,
        ParsedNodesByType $parsedNodesByType,
        ClassReflectionTypesResolver $classReflectionTypesResolver,
        ReflectionProvider $reflectionProvider,
        TypeFactory $typeFactory,
        StaticTypeMapper $staticTypeMapper,
        ObjectTypeSpecifier $objectTypeSpecifier,
        PregMatchTypeCorrector $pregMatchTypeCorrector,
        array $perNodeTypeResolvers
    ) {
        $this->nameResolver = $nameResolver;

        foreach ($perNodeTypeResolvers as $perNodeTypeResolver) {
            $this->addPerNodeTypeResolver($perNodeTypeResolver);
        }

        $this->classReflectionTypesResolver = $classReflectionTypesResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->typeFactory = $typeFactory;
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->parsedNodesByType = $parsedNodesByType;
        $this->betterPhpDocParser = $betterPhpDocParser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->pregMatchTypeCorrector = $pregMatchTypeCorrector;
    }

    /**
     * @param ObjectType|string|mixed $requiredType
     */
    public function isObjectType(Node $node, $requiredType): bool
    {
        $this->ensureRequiredTypeIsStringOrObjectType($requiredType, __METHOD__);

        if (is_string($requiredType) && Strings::contains($requiredType, '*')) {
            return $this->isFnMatch($node, $requiredType);
        }

        $resolvedType = $this->resolve($node);
        if ($resolvedType instanceof MixedType) {
            return false;
        }

        // this should also work with ObjectType and UnionType with ObjectType
        // use PHPStan types here

        if (is_string($requiredType)) {
            $requiredType = new ObjectType($requiredType);
        }

        if ($resolvedType->equals($requiredType)) {
            return true;
        }

        if ($this->isUnionNullTypeOfRequiredType($requiredType, $resolvedType)) {
            return true;
        }

        if ($resolvedType instanceof UnionType) {
            foreach ($resolvedType->getTypes() as $unionedType) {
                if ($unionedType->equals($requiredType)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function resolve(Node $node): Type
    {
        $type = $this->resolveFirstType($node);
        if (! $type instanceof TypeWithClassName) {
            return $type;
        }

        return $this->unionWithParentClassesInterfacesAndUsedTraits($type);
    }

    public function isStringOrUnionStringOnlyType(Node $node): bool
    {
        $nodeType = $this->getStaticType($node);
        if ($nodeType instanceof StringType) {
            return true;
        }

        if ($nodeType instanceof UnionType) {
            foreach ($nodeType->getTypes() as $singleType) {
                if ($singleType->isSuperTypeOf(new StringType())->no()) {
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
        $nodeType = $this->pregMatchTypeCorrector->correct($node, $nodeType);

        if ($this->isCountableObjectType($nodeType)) {
            return true;
        }

        return $this->isArrayType($node);
    }

    public function isArrayType(Node $node): bool
    {
        $nodeStaticType = $this->getStaticType($node);

        $nodeStaticType = $this->pregMatchTypeCorrector->correct($node, $nodeStaticType);
        if ($this->isIntersectionArrayType($nodeStaticType)) {
            return true;
        }

        // PHPStan false positive, when variable has type[] docblock, but default array is missing
        if (($node instanceof PropertyFetch || $node instanceof StaticPropertyFetch) && ! $this->isPropertyFetchWithArrayDefault(
            $node
        )) {
            return false;
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

    public function getStaticType(Node $node): Type
    {
        if ($node instanceof Arg) {
            throw new ShouldNotHappenException('Arg does not have a type, use $arg->value instead');
        }

        if ($node instanceof Param) {
            return $this->resolve($node);
        }

        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);

        if ($node instanceof Scalar) {
            return $this->resolve($node);
        }

        if (! $node instanceof Expr || $nodeScope === null) {
            return new MixedType();
        }

        if ($node instanceof New_ && $this->isAnonymousClass($node->class)) {
            return new ObjectWithoutClassType();
        }

        // false type correction of inherited method
        if ($node instanceof MethodCall && $this->isObjectType($node->var, SplFileInfo::class)) {
            $methodName = $this->nameResolver->getName($node->name);
            if ($methodName === 'getRealPath') {
                return new UnionType([new StringType(), new ConstantBooleanType(false)]);
            }
        }

        $staticType = $nodeScope->getType($node);
        if (! $staticType instanceof ObjectType) {
            return $staticType;
        }

        if ($node instanceof PropertyFetch) {
            // compensate 3rd party non-analysed property reflection
            $vendorPropertyType = $this->getVendorPropertyFetchType($node);
            if ($vendorPropertyType !== null) {
                return $vendorPropertyType;
            }
        }

        return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAlaisedObjectType($node, $staticType);
    }

    public function resolveNodeToPHPStanType(Expr $expr): Type
    {
        if ($this->isArrayType($expr)) {
            $arrayType = $this->getStaticType($expr);
            if ($arrayType instanceof ArrayType) {
                return $arrayType;
            }

            return new ArrayType(new MixedType(), new MixedType());
        }

        return $this->getStaticType($expr);
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

        return is_a($this->getStaticType($node), $staticTypeClass);
    }

    private function addPerNodeTypeResolver(PerNodeTypeResolverInterface $perNodeTypeResolver): void
    {
        foreach ($perNodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->perNodeTypeResolvers[$nodeClass] = $perNodeTypeResolver;
        }
    }

    private function ensureRequiredTypeIsStringOrObjectType($requiredType, string $location): void
    {
        if (is_string($requiredType)) {
            return;
        }

        if ($requiredType instanceof ObjectType) {
            return;
        }

        $reportedType = is_object($requiredType) ? get_class($requiredType) : $requiredType;

        throw new ShouldNotHappenException(sprintf(
            'Value passed to "%s()" must be string or "%s". "%s" given',
            $location,
            ObjectType::class,
            $reportedType
        ));
    }

    private function isFnMatch(Node $node, string $requiredType): bool
    {
        $objectType = $this->resolve($node);

        $classNames = TypeUtils::getDirectClassNames($objectType);
        foreach ($classNames as $className) {
            if (! fnmatch($requiredType, $className, FNM_NOESCAPE)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Matches:
     * - Type|null
     */
    private function isUnionNullTypeOfRequiredType(ObjectType $objectType, Type $resolvedType): bool
    {
        if (! $resolvedType instanceof UnionType) {
            return false;
        }

        if (count($resolvedType->getTypes()) !== 2) {
            return false;
        }

        $firstType = $resolvedType->getTypes()[0];
        $secondType = $resolvedType->getTypes()[1];

        if ($firstType instanceof NullType && $secondType instanceof ObjectType) {
            return $objectType->equals($firstType);
        }

        if ($secondType instanceof NullType && $firstType instanceof ObjectType) {
            return $objectType->equals($secondType);
        }

        return false;
    }

    private function resolveFirstType(Node $node): Type
    {
        foreach ($this->perNodeTypeResolvers as $perNodeTypeResolver) {
            foreach ($perNodeTypeResolver->getNodeClasses() as $nodeClass) {
                if (! is_a($node, $nodeClass)) {
                    continue;
                }

                return $perNodeTypeResolver->resolve($node);
            }
        }

        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope === null) {
            return new MixedType();
        }

        if (! $node instanceof Expr) {
            return new MixedType();
        }

        // skip anonymous classes, ref https://github.com/rectorphp/rector/issues/1574
        if ($node instanceof New_ && $this->isAnonymousClass($node->class)) {
            return new ObjectWithoutClassType();
        }

        $type = $nodeScope->getType($node);

        // hot fix for phpstan not resolving chain method calls
        if ($node instanceof MethodCall && $type instanceof MixedType) {
            return $this->resolveFirstType($node->var);
        }

        return $type;
    }

    private function unionWithParentClassesInterfacesAndUsedTraits(Type $type): Type
    {
        if ($type instanceof TypeWithClassName) {
            if (! ClassExistenceStaticHelper::doesClassLikeExist($type->getClassName())) {
                return $type;
            }

            $allTypes = $this->getClassLikeTypesByClassName($type->getClassName());
            return $this->typeFactory->createObjectTypeOrUnionType($allTypes);
        }

        $classNames = TypeUtils::getDirectClassNames($type);

        $allTypes = [];
        foreach ($classNames as $className) {
            if (! ClassExistenceStaticHelper::doesClassLikeExist($className)) {
                continue;
            }

            $allTypes = array_merge($allTypes, $this->getClassLikeTypesByClassName($className));
        }

        return $this->typeFactory->createObjectTypeOrUnionType($allTypes);
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
        if (! $node instanceof PropertyFetch && ! $node instanceof StaticPropertyFetch) {
            return false;
        }

        /** @var Class_|Trait_|Interface_|null $classNode */
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode instanceof Interface_ || $classNode === null) {
            return false;
        }

        $propertyName = $this->nameResolver->getName($node->name);
        if ($propertyName === null) {
            return false;
        }

        $propertyPropertyNode = $this->getClassNodeProperty($classNode, $propertyName);
        if ($propertyPropertyNode === null) {
            // also possible 3rd party vendor
            if ($node instanceof PropertyFetch) {
                $propertyOwnerStaticType = $this->getStaticType($node->var);
            } else {
                $propertyOwnerStaticType = $this->getStaticType($node->class);
            }

            return ! $propertyOwnerStaticType instanceof ThisType && $propertyOwnerStaticType instanceof TypeWithClassName;
        }

        return $propertyPropertyNode->default instanceof Array_;
    }

    private function isAnonymousClass(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        $className = $this->nameResolver->getName($node);

        return $className === null || Strings::contains($className, 'AnonymousClass');
    }

    /**
     * @return string[]
     */
    private function getClassLikeTypesByClassName(string $className): array
    {
        $classReflection = $this->reflectionProvider->getClass($className);
        $classLikeTypes = $this->classReflectionTypesResolver->resolve($classReflection);

        return array_unique($classLikeTypes);
    }

    /**
     * @param Trait_|Class_ $classLike
     */
    private function getClassNodeProperty(ClassLike $classLike, string $name): ?PropertyProperty
    {
        foreach ($classLike->getProperties() as $property) {
            foreach ($property->props as $propertyProperty) {
                if ($this->nameResolver->isName($propertyProperty, $name)) {
                    return $propertyProperty;
                }
            }
        }

        return null;
    }

    private function getVendorPropertyFetchType(PropertyFetch $propertyFetch): ?Type
    {
        $varObjectType = $this->getStaticType($propertyFetch->var);
        if (! $varObjectType instanceof TypeWithClassName) {
            return null;
        }

        if ($this->parsedNodesByType->findClass($varObjectType->getClassName()) !== null) {
            return null;
        }

        // 3rd party code
        $propertyName = $this->nameResolver->getName($propertyFetch->name);
        if ($propertyName === null) {
            return null;
        }

        if (! property_exists($varObjectType->getClassName(), $propertyName)) {
            return null;
        }

        // property is used
        $propertyReflection = new ReflectionProperty($varObjectType->getClassName(), $propertyName);
        if (! $propertyReflection->getDocComment()) {
            return null;
        }

        $phpDocNode = $this->betterPhpDocParser->parseString((string) $propertyReflection->getDocComment());
        $varTagValues = $phpDocNode->getVarTagValues();

        if (! isset($varTagValues[0])) {
            return null;
        }

        $typeNode = $varTagValues[0]->type;
        if (! $typeNode instanceof TypeNode) {
            return null;
        }

        return $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, new Nop());
    }

    private function isCountableObjectType(Type $type): bool
    {
        if (! $type instanceof ObjectType) {
            return false;
        }

        if (is_a($type->getClassName(), Countable::class, true)) {
            return true;
        }

        // @see https://github.com/rectorphp/rector/issues/2028
        if (is_a($type->getClassName(), 'SimpleXMLElement', true)) {
            return true;
        }

        return is_a($type->getClassName(), 'ResourceBundle', true);
    }
}
