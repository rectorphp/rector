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
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
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
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeCorrector\AccessoryNonEmptyStringTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeCorrector\HasOffsetTypeCorrector;
use Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;
final class NodeTypeResolver
{
    /**
     * @var array<class-string<Node>, NodeTypeResolverInterface>
     */
    private $nodeTypeResolvers = [];
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector
     */
    private $genericClassStringTypeCorrector;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\HasOffsetTypeCorrector
     */
    private $hasOffsetTypeCorrector;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\AccessoryNonEmptyStringTypeCorrector
     */
    private $accessoryNonEmptyStringTypeCorrector;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver
     */
    private $identifierTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @param NodeTypeResolverInterface[] $nodeTypeResolvers
     */
    public function __construct(\Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier $objectTypeSpecifier, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer, \Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector $genericClassStringTypeCorrector, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeTypeResolver\NodeTypeCorrector\HasOffsetTypeCorrector $hasOffsetTypeCorrector, \Rector\NodeTypeResolver\NodeTypeCorrector\AccessoryNonEmptyStringTypeCorrector $accessoryNonEmptyStringTypeCorrector, \Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver $identifierTypeResolver, \Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, array $nodeTypeResolvers)
    {
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->classAnalyzer = $classAnalyzer;
        $this->genericClassStringTypeCorrector = $genericClassStringTypeCorrector;
        $this->reflectionProvider = $reflectionProvider;
        $this->hasOffsetTypeCorrector = $hasOffsetTypeCorrector;
        $this->accessoryNonEmptyStringTypeCorrector = $accessoryNonEmptyStringTypeCorrector;
        $this->identifierTypeResolver = $identifierTypeResolver;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->betterNodeFinder = $betterNodeFinder;
        foreach ($nodeTypeResolvers as $nodeTypeResolver) {
            $this->addNodeTypeResolver($nodeTypeResolver);
        }
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
    public function getType(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        if ($node instanceof \PhpParser\Node\NullableType) {
            if ($node->type instanceof \PhpParser\Node\Name && $node->type->hasAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NAMESPACED_NAME)) {
                $node->type = new \PhpParser\Node\Name\FullyQualified($node->type->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NAMESPACED_NAME));
            }
            $type = $this->getType($node->type);
            if (!$type instanceof \PHPStan\Type\MixedType) {
                return new \PHPStan\Type\UnionType([$type, new \PHPStan\Type\NullType()]);
            }
        }
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
                $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
                $type = $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $type, $scope);
            }
            return $this->hasOffsetTypeCorrector->correct($type);
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            if ($node instanceof \PhpParser\Node\Expr\ConstFetch) {
                $name = $node->name->toString();
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
        $type = $scope->getNativeType($expr);
        return $this->accessoryNonEmptyStringTypeCorrector->correct($type);
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
        $class = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        return $this->isObjectType($class, $objectType);
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
