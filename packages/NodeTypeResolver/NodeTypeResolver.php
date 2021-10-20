<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
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
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeCorrector\AccessoryNonEmptyStringTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeCorrector\HasOffsetTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;
use RectorPrefix20211020\Symfony\Contracts\Service\Attribute\Required;
final class NodeTypeResolver
{
    /**
     * @var array<class-string<Node>, NodeTypeResolverInterface>
     */
    private $nodeTypeResolvers = [];
    /**
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;
    /**
     * @var \Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;
    /**
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector
     */
    private $genericClassStringTypeCorrector;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\HasOffsetTypeCorrector
     */
    private $hasOffsetTypeCorrector;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\AccessoryNonEmptyStringTypeCorrector
     */
    private $accessoryNonEmptyStringTypeCorrector;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver
     */
    private $identifierTypeResolver;
    /**
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @param NodeTypeResolverInterface[] $nodeTypeResolvers
     */
    public function __construct(\Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier $objectTypeSpecifier, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer, \Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector $genericClassStringTypeCorrector, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeTypeResolver\NodeTypeCorrector\HasOffsetTypeCorrector $hasOffsetTypeCorrector, \Rector\NodeTypeResolver\NodeTypeCorrector\AccessoryNonEmptyStringTypeCorrector $accessoryNonEmptyStringTypeCorrector, \Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver $identifierTypeResolver, \Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector, array $nodeTypeResolvers)
    {
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->classAnalyzer = $classAnalyzer;
        $this->genericClassStringTypeCorrector = $genericClassStringTypeCorrector;
        $this->reflectionProvider = $reflectionProvider;
        $this->hasOffsetTypeCorrector = $hasOffsetTypeCorrector;
        $this->accessoryNonEmptyStringTypeCorrector = $accessoryNonEmptyStringTypeCorrector;
        $this->identifierTypeResolver = $identifierTypeResolver;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        foreach ($nodeTypeResolvers as $nodeTypeResolver) {
            $this->addNodeTypeResolver($nodeTypeResolver);
        }
    }
    // Prevents circular dependency
    /**
     * @required
     */
    public function autowireNodeTypeResolver(\Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer $arrayTypeAnalyzer) : void
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
    }
    /**
     * @param ObjectType[] $requiredTypes
     */
    public function isObjectTypes(\PhpParser\Node $node, array $requiredTypes) : bool
    {
        foreach ($requiredTypes as $requiredType) {
            if ($this->isObjectType($node, $requiredType)) {
                return \true;
            }
        }
        return \false;
    }
    public function isObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $requiredObjectType) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return \false;
        }
        $resolvedType = $this->getType($node);
        if ($resolvedType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        if ($resolvedType instanceof \PHPStan\Type\ThisType) {
            $resolvedType = $resolvedType->getStaticObjectType();
        }
        if ($resolvedType instanceof \PHPStan\Type\ObjectType) {
            return $this->resolveObjectType($resolvedType, $requiredObjectType);
        }
        return $this->isMatchingUnionType($resolvedType, $requiredObjectType);
    }
    /**
     * @deprecated
     * @see use NodeTypeResolver::getType() instead
     */
    public function resolve(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        $errorMessage = \sprintf('Method "%s" is deprecated. Use "getType()" instead', __METHOD__);
        \trigger_error($errorMessage, \E_USER_WARNING);
        \sleep(3);
        return $this->getType($node);
    }
    public function getType(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        if ($node instanceof \PhpParser\Node\Expr\Ternary) {
            $ternaryType = $this->resolveTernaryType($node);
            if (!$ternaryType instanceof \PHPStan\Type\MixedType) {
                return $ternaryType;
            }
        }
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
            $first = $this->getType($node->left);
            $second = $this->getType($node->right);
            if ($this->isUnionTypeable($first, $second)) {
                return new \PHPStan\Type\UnionType([$first, $second]);
            }
        }
        $type = $this->resolveByNodeTypeResolvers($node);
        if ($type !== null) {
            $type = $this->accessoryNonEmptyStringTypeCorrector->correct($type);
            $type = $this->genericClassStringTypeCorrector->correct($type);
            if ($type instanceof \PHPStan\Type\ObjectType) {
                // we want to keep aliased object types
                $type = $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $type);
            }
            return $this->hasOffsetTypeCorrector->correct($type);
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            if ($node instanceof \PhpParser\Node\Expr\ConstFetch && $node->name instanceof \PhpParser\Node\Name) {
                $name = (string) $node->name;
                if (\strtolower($name) === 'null') {
                    return new \PHPStan\Type\NullType();
                }
            }
            return new \PHPStan\Type\MixedType();
        }
        if (!$node instanceof \PhpParser\Node\Expr) {
            // scalar type, e.g. from param type name
            if ($node instanceof \PhpParser\Node\Identifier) {
                return $this->identifierTypeResolver->resolve($node);
            }
            return new \PHPStan\Type\MixedType();
        }
        // skip anonymous classes, ref https://github.com/rectorphp/rector/issues/1574
        if ($node instanceof \PhpParser\Node\Expr\New_ && $this->classAnalyzer->isAnonymousClass($node->class)) {
            return new \PHPStan\Type\ObjectWithoutClassType();
        }
        $type = $scope->getType($node);
        $type = $this->accessoryNonEmptyStringTypeCorrector->correct($type);
        $type = $this->genericClassStringTypeCorrector->correct($type);
        // hot fix for phpstan not resolving chain method calls
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $type;
        }
        if (!$type instanceof \PHPStan\Type\MixedType) {
            return $type;
        }
        return $this->getType($node->var);
    }
    /**
     * e.g. string|null, ObjectNull|null
     */
    public function isNullableType(\PhpParser\Node $node) : bool
    {
        $nodeType = $this->getType($node);
        return \PHPStan\Type\TypeCombinator::containsNull($nodeType);
    }
    public function getNativeType(\PhpParser\Node\Expr $expr) : \PHPStan\Type\Type
    {
        $scope = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return new \PHPStan\Type\MixedType();
        }
        return $scope->getNativeType($expr);
    }
    /**
     * @deprecated
     * @see Use NodeTypeResolver::getType() instead
     */
    public function getStaticType(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        $errorMessage = \sprintf('Method "%s" is deprecated. Use "getType()" instead', __METHOD__);
        \trigger_error($errorMessage, \E_USER_WARNING);
        \sleep(3);
        if ($node instanceof \PhpParser\Node\Param || $node instanceof \PhpParser\Node\Expr\New_ || $node instanceof \PhpParser\Node\Stmt\Return_) {
            return $this->getType($node);
        }
        if (!$node instanceof \PhpParser\Node\Expr) {
            return new \PHPStan\Type\MixedType();
        }
        if ($this->arrayTypeAnalyzer->isArrayType($node)) {
            return $this->resolveArrayType($node);
        }
        if ($node instanceof \PhpParser\Node\Scalar) {
            return $this->getType($node);
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return new \PHPStan\Type\MixedType();
        }
        $staticType = $scope->getType($node);
        if ($staticType instanceof \PHPStan\Type\Generic\GenericObjectType) {
            return $staticType;
        }
        if ($staticType instanceof \PHPStan\Type\ObjectType) {
            return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $staticType);
        }
        return $this->accessoryNonEmptyStringTypeCorrector->correct($staticType);
    }
    public function isNumberType(\PhpParser\Node $node) : bool
    {
        $nodeType = $this->getType($node);
        if ($nodeType instanceof \PHPStan\Type\IntegerType) {
            return \true;
        }
        return $nodeType instanceof \PHPStan\Type\FloatType;
    }
    /**
     * @param class-string<Type> $desiredType
     */
    public function isNullableTypeOfSpecificType(\PhpParser\Node $node, string $desiredType) : bool
    {
        $nodeType = $this->getType($node);
        if (!$nodeType instanceof \PHPStan\Type\UnionType) {
            return \false;
        }
        if (!\PHPStan\Type\TypeCombinator::containsNull($nodeType)) {
            return \false;
        }
        $bareType = \PHPStan\Type\TypeCombinator::removeNull($nodeType);
        return \is_a($bareType, $desiredType, \true);
    }
    /**
     * @return class-string
     */
    public function getFullyQualifiedClassName(\PHPStan\Type\TypeWithClassName $typeWithClassName) : string
    {
        if ($typeWithClassName instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        return $typeWithClassName->getClassName();
    }
    public function isMethodStaticCallOrClassMethodObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            // method call is variable return
            return $this->isObjectType($node->var, $objectType);
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->isObjectType($node->class, $objectType);
        }
        $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        return $this->isObjectType($classLike, $objectType);
    }
    private function isUnionTypeable(\PHPStan\Type\Type $first, \PHPStan\Type\Type $second) : bool
    {
        return !$first instanceof \PHPStan\Type\UnionType && !$second instanceof \PHPStan\Type\UnionType && !$second instanceof \PHPStan\Type\NullType;
    }
    private function addNodeTypeResolver(\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface $nodeTypeResolver) : void
    {
        foreach ($nodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->nodeTypeResolvers[$nodeClass] = $nodeTypeResolver;
        }
    }
    private function isMatchingUnionType(\PHPStan\Type\Type $resolvedType, \PHPStan\Type\ObjectType $requiredObjectType) : bool
    {
        $type = \PHPStan\Type\TypeCombinator::removeNull($resolvedType);
        // for falsy nullables
        $type = \PHPStan\Type\TypeCombinator::remove($type, new \PHPStan\Type\Constant\ConstantBooleanType(\false));
        if (!$type instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        return $type->isInstanceOf($requiredObjectType->getClassName())->yes();
    }
    private function resolveArrayType(\PhpParser\Node\Expr $expr) : \PHPStan\Type\Type
    {
        /** @var Scope|null $scope */
        $scope = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($scope instanceof \PHPStan\Analyser\Scope) {
            $arrayType = $scope->getType($expr);
            $arrayType = $this->genericClassStringTypeCorrector->correct($arrayType);
            return $this->removeNonEmptyArrayFromIntersectionWithArrayType($arrayType);
        }
        return new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
    }
    private function resolveByNodeTypeResolvers(\PhpParser\Node $node) : ?\PHPStan\Type\Type
    {
        foreach ($this->nodeTypeResolvers as $nodeClass => $nodeTypeResolver) {
            if (!\is_a($node, $nodeClass, \true)) {
                continue;
            }
            return $nodeTypeResolver->resolve($node);
        }
        return null;
    }
    private function removeNonEmptyArrayFromIntersectionWithArrayType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if (!$type instanceof \PHPStan\Type\IntersectionType) {
            return $type;
        }
        if (\count($type->getTypes()) !== 2) {
            return $type;
        }
        if (!$type->isSubTypeOf(new \PHPStan\Type\Accessory\NonEmptyArrayType())->yes()) {
            return $type;
        }
        $otherType = null;
        foreach ($type->getTypes() as $intersectionedType) {
            if ($intersectionedType instanceof \PHPStan\Type\Accessory\NonEmptyArrayType) {
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
    private function isObjectTypeOfObjectType(\PHPStan\Type\ObjectType $resolvedObjectType, \PHPStan\Type\ObjectType $requiredObjectType) : bool
    {
        if ($resolvedObjectType->isInstanceOf($requiredObjectType->getClassName())->yes()) {
            return \true;
        }
        if ($resolvedObjectType->getClassName() === $requiredObjectType->getClassName()) {
            return \true;
        }
        if (!$this->reflectionProvider->hasClass($resolvedObjectType->getClassName())) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($resolvedObjectType->getClassName());
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if ($ancestorClassReflection->hasTraitUse($requiredObjectType->getClassName())) {
                return \true;
            }
        }
        return $classReflection->isSubclassOf($requiredObjectType->getClassName());
    }
    private function resolveObjectType(\PHPStan\Type\ObjectType $resolvedObjectType, \PHPStan\Type\ObjectType $requiredObjectType) : bool
    {
        $renamedObjectType = $this->renamedClassesDataCollector->matchClassName($resolvedObjectType);
        if (!$renamedObjectType instanceof \PHPStan\Type\ObjectType) {
            return $this->isObjectTypeOfObjectType($resolvedObjectType, $requiredObjectType);
        }
        if (!$this->isObjectTypeOfObjectType($renamedObjectType, $requiredObjectType)) {
            return $this->isObjectTypeOfObjectType($resolvedObjectType, $requiredObjectType);
        }
        return \true;
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\UnionType
     */
    private function resolveTernaryType(\PhpParser\Node\Expr\Ternary $ternary)
    {
        if ($ternary->if !== null) {
            $first = $this->getType($ternary->if);
            $second = $this->getType($ternary->else);
            if ($this->isUnionTypeable($first, $second)) {
                return new \PHPStan\Type\UnionType([$first, $second]);
            }
        }
        $condType = $this->getType($ternary->cond);
        if ($this->isNullableType($ternary->cond) && $condType instanceof \PHPStan\Type\UnionType) {
            $first = $condType->getTypes()[0];
            $second = $this->getType($ternary->else);
            if ($this->isUnionTypeable($first, $second)) {
                return new \PHPStan\Type\UnionType([$first, $second]);
            }
        }
        return new \PHPStan\Type\MixedType();
    }
}
