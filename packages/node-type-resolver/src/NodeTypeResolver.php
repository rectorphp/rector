<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeCorrector\ParentClassLikeTypeCorrector;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Rector\StaticTypeMapper\TypeFactory\TypeFactoryStaticHelper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;

final class NodeTypeResolver
{
    /**
     * @var array<class-string, NodeTypeResolverInterface>
     */
    private $nodeTypeResolvers = [];

    /**
     * @var ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;

    /**
     * @var ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;

    /**
     * @var ParentClassLikeTypeCorrector
     */
    private $parentClassLikeTypeCorrector;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    /**
     * @param NodeTypeResolverInterface[] $nodeTypeResolvers
     */
    public function __construct(
        ObjectTypeSpecifier $objectTypeSpecifier,
        ParentClassLikeTypeCorrector $parentClassLikeTypeCorrector,
        TypeUnwrapper $typeUnwrapper,
        ClassAnalyzer $classAnalyzer,
        array $nodeTypeResolvers
    ) {
        foreach ($nodeTypeResolvers as $nodeTypeResolver) {
            $this->addNodeTypeResolver($nodeTypeResolver);
        }

        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->parentClassLikeTypeCorrector = $parentClassLikeTypeCorrector;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->classAnalyzer = $classAnalyzer;
    }

    /**
     * Prevents circular dependency
     * @required
     */
    public function autowireNodeTypeResolver(ArrayTypeAnalyzer $arrayTypeAnalyzer): void
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
    }

    /**
     * @param string[]|ObjectType[] $requiredTypes
     */
    public function isObjectTypes(Node $node, array $requiredTypes): bool
    {
        foreach ($requiredTypes as $requiredType) {
            if ($this->isObjectType($node, $requiredType)) {
                return true;
            }
        }

        return false;
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

        return $this->isMatchingUnionType($requiredType, $resolvedType);
    }

    public function resolve(Node $node): Type
    {
        $type = $this->resolveFirstType($node);
        if (! $type instanceof TypeWithClassName) {
            return $type;
        }

        return $this->parentClassLikeTypeCorrector->correct($type);
    }

    /**
     * e.g. string|null, ObjectNull|null
     */
    public function isNullableType(Node $node): bool
    {
        $nodeType = $this->resolve($node);
        if (! $nodeType instanceof UnionType) {
            return false;
        }

        return $nodeType->isSuperTypeOf(new NullType())
            ->yes();
    }

    /**
     * @deprecated
     * Use @see NodeTypeResolver::resolve() instead
     */
    public function getStaticType(Node $node): Type
    {
        if ($this->isArrayExpr($node)) {
            /** @var Expr $node */
            return $this->resolveArrayType($node);
        }

        if ($node instanceof Arg) {
            $node = $node->value;
        }

        if (StaticInstanceOf::isOneOf($node, [Param::class, Scalar::class])) {
            return $this->resolve($node);
        }

        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $node instanceof Expr) {
            return new MixedType();
        }
        if (! $nodeScope instanceof Scope) {
            return new MixedType();
        }

        if ($node instanceof New_) {
            $isAnonymousClass = $this->classAnalyzer->isAnonymousClass($node->class);
            if ($isAnonymousClass) {
                return $this->resolveAnonymousClassType($node);
            }
        }

        $staticType = $nodeScope->getType($node);
        if (! $staticType instanceof ObjectType) {
            return $staticType;
        }

        return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $staticType);
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

        return is_a($this->resolve($node), $staticTypeClass);
    }

    /**
     * @param ObjectType|string $desiredType
     */
    public function isObjectTypeOrNullableObjectType(Node $node, $desiredType): bool
    {
        if ($this->isObjectType($node, $desiredType)) {
            return true;
        }

        $nodeType = $this->getStaticType($node);
        if (! $nodeType instanceof UnionType) {
            return false;
        }

        $unwrappedNodeType = $this->typeUnwrapper->unwrapNullableType($nodeType);
        if (! $unwrappedNodeType instanceof TypeWithClassName) {
            return false;
        }

        $desiredTypeString = $desiredType instanceof ObjectType ? $desiredType->getClassName() : $desiredType;
        return is_a($unwrappedNodeType->getClassName(), $desiredTypeString, true);
    }

    public function isNullableObjectType(Node $node): bool
    {
        return $this->isNullableTypeOfSpecificType($node, ObjectType::class);
    }

    public function isNullableArrayType(Node $node): bool
    {
        return $this->isNullableTypeOfSpecificType($node, ArrayType::class);
    }

    public function isNullableTypeOfSpecificType(Node $node, string $desiredType): bool
    {
        $nodeType = $this->resolve($node);
        if (! $nodeType instanceof UnionType) {
            return false;
        }

        if ($nodeType->isSuperTypeOf(new NullType())->no()) {
            return false;
        }

        if (count($nodeType->getTypes()) !== 2) {
            return false;
        }

        foreach ($nodeType->getTypes() as $type) {
            if (is_a($type, $desiredType, true)) {
                return true;
            }
        }

        return false;
    }

    public function isPropertyBoolean(Property $property): bool
    {
        if ($this->isStaticType($property, BooleanType::class)) {
            return true;
        }

        $defaultNodeValue = $property->props[0]->default;
        if (! $defaultNodeValue instanceof \PhpParser\Node\Expr) {
            return false;
        }

        return $this->isStaticType($defaultNodeValue, BooleanType::class);
    }

    public function getFullyQualifiedClassName(TypeWithClassName $typeWithClassName): string
    {
        if ($typeWithClassName instanceof ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }

        return $typeWithClassName->getClassName();
    }

    /**
     * @param Type[] $desiredTypes
     */
    public function isSameObjectTypes(ObjectType $objectType, array $desiredTypes): bool
    {
        foreach ($desiredTypes as $abstractClassConstructorParamType) {
            if ($abstractClassConstructorParamType->equals($objectType)) {
                return true;
            }
        }

        return false;
    }

    public function isMethodStaticCallOrClassMethodObjectType(Node $node, string $type): bool
    {
        if ($node instanceof MethodCall) {
            // method call is variable return
            return $this->isObjectType($node->var, $type);
        }

        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $type);
        }

        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        return $this->isObjectType($classLike, $type);
    }

    private function addNodeTypeResolver(NodeTypeResolverInterface $nodeTypeResolver): void
    {
        foreach ($nodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->nodeTypeResolvers[$nodeClass] = $nodeTypeResolver;
        }
    }

    /**
     * @param ObjectType|string|mixed $requiredType
     */
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

    private function isMatchingUnionType(Type $requiredType, Type $resolvedType): bool
    {
        if (! $resolvedType instanceof UnionType) {
            return false;
        }

        foreach ($resolvedType->getTypes() as $unionedType) {
            if ($unionedType instanceof TypeWithClassName && $requiredType instanceof TypeWithClassName && is_a(
                $unionedType->getClassName(),
                $requiredType->getClassName(),
                true
            )) {
                return true;
            }

            if (! $unionedType->equals($requiredType)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function resolveFirstType(Node $node): Type
    {
        $type = $this->resolveByNodeTypeResolvers($node);
        if ($type !== null) {
            return $type;
        }

        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $nodeScope instanceof Scope) {
            return new MixedType();
        }
        if (! $node instanceof Expr) {
            return new MixedType();
        }

        // skip anonymous classes, ref https://github.com/rectorphp/rector/issues/1574
        if ($node instanceof New_) {
            $isAnonymousClass = $this->classAnalyzer->isAnonymousClass($node->class);
            if ($isAnonymousClass) {
                return new ObjectWithoutClassType();
            }
        }

        $type = $nodeScope->getType($node);

        // hot fix for phpstan not resolving chain method calls
        if ($node instanceof MethodCall && $type instanceof MixedType) {
            return $this->resolveFirstType($node->var);
        }

        return $type;
    }

    private function isArrayExpr(Node $node): bool
    {
        if (! $node instanceof Expr) {
            return false;
        }
        return $this->arrayTypeAnalyzer->isArrayType($node);
    }

    private function resolveArrayType(Expr $expr): Type
    {
        /** @var Scope|null $scope */
        $scope = $expr->getAttribute(AttributeKey::SCOPE);

        if ($scope instanceof Scope) {
            $arrayType = $scope->getType($expr);
            if ($arrayType !== null) {
                return $this->removeNonEmptyArrayFromIntersectionWithArrayType($arrayType);
            }
        }

        return new ArrayType(new MixedType(), new MixedType());
    }

    private function resolveAnonymousClassType(New_ $new): ObjectWithoutClassType
    {
        if (! $new->class instanceof Class_) {
            return new ObjectWithoutClassType();
        }

        $types = [];

        /** @var Class_ $class */
        $class = $new->class;
        if ($class->extends !== null) {
            $parentClass = (string) $class->extends;
            $types[] = new FullyQualifiedObjectType($parentClass);
        }

        foreach ($class->implements as $implement) {
            $parentClass = (string) $implement;
            $types[] = new FullyQualifiedObjectType($parentClass);
        }

        if (count($types) > 1) {
            $unionType = TypeFactoryStaticHelper::createUnionObjectType($types);
            return new ObjectWithoutClassType($unionType);
        }

        if (count($types) === 1) {
            return new ObjectWithoutClassType($types[0]);
        }

        return new ObjectWithoutClassType();
    }

    private function resolveByNodeTypeResolvers(Node $node): ?Type
    {
        foreach ($this->nodeTypeResolvers as $nodeClass => $nodeTypeResolver) {
            if (! is_a($node, $nodeClass)) {
                continue;
            }

            return $nodeTypeResolver->resolve($node);
        }

        return null;
    }

    private function removeNonEmptyArrayFromIntersectionWithArrayType(Type $type): Type
    {
        if (! $type instanceof IntersectionType) {
            return $type;
        }

        if (count($type->getTypes()) !== 2) {
            return $type;
        }

        if (! $type->isSubTypeOf(new NonEmptyArrayType())->yes()) {
            return $type;
        }

        $otherType = null;
        foreach ($type->getTypes() as $intersectionedType) {
            if ($intersectionedType instanceof NonEmptyArrayType) {
                continue;
            }

            $otherType = $intersectionedType;
            break;
        }

        if ($otherType === null) {
            return $type;
        }

        return $otherType;
    }
}
