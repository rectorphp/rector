<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeCorrector\HasOffsetTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;
use Symfony\Contracts\Service\Attribute\Required;

final class NodeTypeResolver
{
    /**
     * @var array<class-string<Node>, NodeTypeResolverInterface>
     */
    private array $nodeTypeResolvers = [];

    private ArrayTypeAnalyzer $arrayTypeAnalyzer;

    /**
     * @param NodeTypeResolverInterface[] $nodeTypeResolvers
     */
    public function __construct(
        private ObjectTypeSpecifier $objectTypeSpecifier,
        private ClassAnalyzer $classAnalyzer,
        private GenericClassStringTypeCorrector $genericClassStringTypeCorrector,
        private ReflectionProvider $reflectionProvider,
        private HasOffsetTypeCorrector $hasOffsetTypeCorrector,
        private IdentifierTypeResolver $identifierTypeResolver,
        private RenamedClassesDataCollector $renamedClassesDataCollector,
        array $nodeTypeResolvers
    ) {
        foreach ($nodeTypeResolvers as $nodeTypeResolver) {
            $this->addNodeTypeResolver($nodeTypeResolver);
        }
    }

    // Prevents circular dependency

    #[Required]
    public function autowireNodeTypeResolver(ArrayTypeAnalyzer $arrayTypeAnalyzer): void
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
    }

    /**
     * @param ObjectType[] $requiredTypes
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

    public function isObjectType(Node $node, ObjectType $requiredObjectType): bool
    {
        if ($node instanceof ClassConstFetch) {
            return false;
        }

        $resolvedType = $this->resolve($node);
        if ($resolvedType instanceof MixedType) {
            return false;
        }

        if ($resolvedType instanceof ThisType) {
            $resolvedType = $resolvedType->getStaticObjectType();
        }

        if ($resolvedType instanceof ObjectType) {
            return $this->resolveObjectType($resolvedType, $requiredObjectType);
        }

        return $this->isMatchingUnionType($resolvedType, $requiredObjectType);
    }

    public function resolve(Node $node): Type
    {
        $type = $this->resolveByNodeTypeResolvers($node);
        if ($type !== null) {
            return $this->hasOffsetTypeCorrector->correct($type);
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);

        if (! $scope instanceof Scope) {
            if ($node instanceof ConstFetch && $node->name instanceof Name) {
                $name = (string) $node->name;
                if (strtolower($name) === 'null') {
                    return new NullType();
                }
            }

            return new MixedType();
        }

        if (! $node instanceof Expr) {
            // scalar type, e.g. from param type name
            if ($node instanceof Identifier) {
                return $this->identifierTypeResolver->resolve($node);
            }

            return new MixedType();
        }

        // skip anonymous classes, ref https://github.com/rectorphp/rector/issues/1574
        if ($node instanceof New_ && $this->classAnalyzer->isAnonymousClass($node->class)) {
            return new ObjectWithoutClassType();
        }

        $type = $scope->getType($node);

        // hot fix for phpstan not resolving chain method calls
        if (! $node instanceof MethodCall) {
            return $type;
        }

        if (! $type instanceof MixedType) {
            return $type;
        }

        return $this->resolve($node->var);
    }

    /**
     * e.g. string|null, ObjectNull|null
     */
    public function isNullableType(Node $node): bool
    {
        $nodeType = $this->resolve($node);
        return TypeCombinator::containsNull($nodeType);
    }

    public function getNativeType(Expr $expr): Type
    {
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return new MixedType();
        }

        return $scope->getNativeType($expr);
    }

    public function getStaticType(Node $node): Type
    {
        if ($node instanceof Param) {
            return $this->resolve($node);
        }

        if ($node instanceof New_) {
            return $this->resolve($node);
        }

        if ($node instanceof Return_) {
            return $this->resolve($node);
        }

        if (! $node instanceof Expr) {
            return new MixedType();
        }

        if ($this->arrayTypeAnalyzer->isArrayType($node)) {
            return $this->resolveArrayType($node);
        }

        if ($node instanceof Scalar) {
            return $this->resolve($node);
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return new MixedType();
        }

        $staticType = $scope->getType($node);
        if ($staticType instanceof GenericObjectType) {
            return $staticType;
        }

        if ($staticType instanceof ObjectType) {
            return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $staticType);
        }

        return $staticType;
    }

    public function isNumberType(Node $node): bool
    {
        if ($this->isStaticType($node, IntegerType::class)) {
            return true;
        }

        return $this->isStaticType($node, FloatType::class);
    }

    /**
     * @param class-string<Type> $staticTypeClass
     */
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
     * @param class-string<Type> $desiredType
     */
    public function isNullableTypeOfSpecificType(Node $node, string $desiredType): bool
    {
        $nodeType = $this->resolve($node);
        if (! $nodeType instanceof UnionType) {
            return false;
        }

        if (! TypeCombinator::containsNull($nodeType)) {
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
        if (! $defaultNodeValue instanceof Expr) {
            return false;
        }

        return $this->isStaticType($defaultNodeValue, BooleanType::class);
    }

    /**
     * @return class-string
     */
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
        foreach ($desiredTypes as $desiredType) {
            $desiredTypeEquals = $desiredType->equals($objectType);
            if ($desiredTypeEquals) {
                return true;
            }
        }

        return false;
    }

    public function isMethodStaticCallOrClassMethodObjectType(Node $node, ObjectType $objectType): bool
    {
        if ($node instanceof MethodCall) {
            // method call is variable return
            return $this->isObjectType($node->var, $objectType);
        }

        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $objectType);
        }

        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        return $this->isObjectType($classLike, $objectType);
    }

    public function resolveObjectTypeFromScope(Scope $scope): ?ObjectType
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $className = $classReflection->getName();
        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        return new ObjectType($className, null, $classReflection);
    }

    private function addNodeTypeResolver(NodeTypeResolverInterface $nodeTypeResolver): void
    {
        foreach ($nodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->nodeTypeResolvers[$nodeClass] = $nodeTypeResolver;
        }
    }

    private function isMatchingUnionType(Type $resolvedType, ObjectType $requiredObjectType): bool
    {
        $type = TypeCombinator::removeNull($resolvedType);
        // for falsy nullables
        $type = TypeCombinator::remove($type, new ConstantBooleanType(false));

        if (! $type instanceof ObjectType) {
            return false;
        }

        return $type->isInstanceOf($requiredObjectType->getClassName())
            ->yes();
    }

    private function resolveArrayType(Expr $expr): Type
    {
        /** @var Scope|null $scope */
        $scope = $expr->getAttribute(AttributeKey::SCOPE);

        if ($scope instanceof Scope) {
            $arrayType = $scope->getType($expr);
            $arrayType = $this->genericClassStringTypeCorrector->correct($arrayType);
            return $this->removeNonEmptyArrayFromIntersectionWithArrayType($arrayType);
        }

        return new ArrayType(new MixedType(), new MixedType());
    }

    private function resolveByNodeTypeResolvers(Node $node): ?Type
    {
        foreach ($this->nodeTypeResolvers as $nodeClass => $nodeTypeResolver) {
            if (! is_a($node, $nodeClass, true)) {
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

    private function isObjectTypeOfObjectType(ObjectType $resolvedObjectType, ObjectType $requiredObjectType): bool
    {
        if ($resolvedObjectType->isInstanceOf($requiredObjectType->getClassName())->yes()) {
            return true;
        }

        if ($resolvedObjectType->getClassName() === $requiredObjectType->getClassName()) {
            return true;
        }

        if (! $this->reflectionProvider->hasClass($resolvedObjectType->getClassName())) {
            return false;
        }

        $classReflection = $this->reflectionProvider->getClass($resolvedObjectType->getClassName());
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if ($ancestorClassReflection->hasTraitUse($requiredObjectType->getClassName())) {
                return true;
            }
        }

        return $classReflection->isSubclassOf($requiredObjectType->getClassName());
    }

    private function resolveObjectType(ObjectType $resolvedObjectType, ObjectType $requiredObjectType): bool
    {
        $renamedObjectType = $this->renamedClassesDataCollector->matchClassName($resolvedObjectType);
        if (! $renamedObjectType instanceof ObjectType) {
            return $this->isObjectTypeOfObjectType($resolvedObjectType, $requiredObjectType);
        }

        if (! $this->isObjectTypeOfObjectType($renamedObjectType, $requiredObjectType)) {
            return $this->isObjectTypeOfObjectType($resolvedObjectType, $requiredObjectType);
        }
        return true;
    }
}
