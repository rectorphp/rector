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
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\ClassAutoloadingException;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantBooleanType;
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
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\ObjectTypeSpecifier;
final class NodeTypeResolver
{
    /**
     * @var array<class-string<Node>, NodeTypeResolverInterface>
     */
    private $nodeTypeResolvers = [];
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PHPStan\ObjectTypeSpecifier
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
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @param NodeTypeResolverInterface[] $nodeTypeResolvers
     */
    public function __construct(ObjectTypeSpecifier $objectTypeSpecifier, ClassAnalyzer $classAnalyzer, GenericClassStringTypeCorrector $genericClassStringTypeCorrector, ReflectionProvider $reflectionProvider, HasOffsetTypeCorrector $hasOffsetTypeCorrector, AccessoryNonEmptyStringTypeCorrector $accessoryNonEmptyStringTypeCorrector, RenamedClassesDataCollector $renamedClassesDataCollector, array $nodeTypeResolvers)
    {
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->classAnalyzer = $classAnalyzer;
        $this->genericClassStringTypeCorrector = $genericClassStringTypeCorrector;
        $this->reflectionProvider = $reflectionProvider;
        $this->hasOffsetTypeCorrector = $hasOffsetTypeCorrector;
        $this->accessoryNonEmptyStringTypeCorrector = $accessoryNonEmptyStringTypeCorrector;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        foreach ($nodeTypeResolvers as $nodeTypeResolver) {
            foreach ($nodeTypeResolver->getNodeClasses() as $nodeClass) {
                $this->nodeTypeResolvers[$nodeClass] = $nodeTypeResolver;
            }
        }
    }
    /**
     * @api doctrine symfony
     * @param ObjectType[] $requiredTypes
     */
    public function isObjectTypes(Node $node, array $requiredTypes) : bool
    {
        foreach ($requiredTypes as $requiredType) {
            if ($this->isObjectType($node, $requiredType)) {
                return \true;
            }
        }
        return \false;
    }
    public function isObjectType(Node $node, ObjectType $requiredObjectType) : bool
    {
        if ($node instanceof ClassConstFetch) {
            return \false;
        }
        $resolvedType = $this->getType($node);
        if ($resolvedType instanceof MixedType) {
            return \false;
        }
        if ($resolvedType instanceof ThisType) {
            $resolvedType = $resolvedType->getStaticObjectType();
        }
        if ($resolvedType instanceof ObjectType) {
            try {
                return $this->resolveObjectType($resolvedType, $requiredObjectType);
            } catch (ClassAutoloadingException $exception) {
                // in some type checks, the provided type in rector.php configuration does not have to exists
                return \false;
            }
        }
        return $this->isMatchingUnionType($resolvedType, $requiredObjectType);
    }
    public function getType(Node $node) : Type
    {
        if ($node instanceof Property && $node->type instanceof NullableType) {
            return $this->getType($node->type);
        }
        if ($node instanceof NullableType) {
            if ($node->type instanceof Name && $node->type->hasAttribute(AttributeKey::NAMESPACED_NAME)) {
                $node->type = new FullyQualified($node->type->getAttribute(AttributeKey::NAMESPACED_NAME));
            }
            $type = $this->getType($node->type);
            if (!$type instanceof MixedType) {
                return new UnionType([$type, new NullType()]);
            }
        }
        if ($node instanceof Ternary) {
            $ternaryType = $this->resolveTernaryType($node);
            if (!$ternaryType instanceof MixedType) {
                return $ternaryType;
            }
        }
        if ($node instanceof Coalesce) {
            $first = $this->getType($node->left);
            $second = $this->getType($node->right);
            if ($this->isUnionTypeable($first, $second)) {
                return new UnionType([$first, $second]);
            }
        }
        $type = $this->resolveByNodeTypeResolvers($node);
        if ($type instanceof Type) {
            $type = $this->accessoryNonEmptyStringTypeCorrector->correct($type);
            $type = $this->genericClassStringTypeCorrector->correct($type);
            if ($type instanceof ObjectType) {
                $scope = $node->getAttribute(AttributeKey::SCOPE);
                $type = $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $type, $scope);
            }
            return $this->hasOffsetTypeCorrector->correct($type);
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            if ($node instanceof ConstFetch) {
                $name = $node->name->toString();
                if (\strtolower($name) === 'null') {
                    return new NullType();
                }
            }
            return new MixedType();
        }
        if (!$node instanceof Expr) {
            return new MixedType();
        }
        // skip anonymous classes, ref https://github.com/rectorphp/rector/issues/1574
        if ($node instanceof New_ && $this->classAnalyzer->isAnonymousClass($node->class)) {
            return new ObjectWithoutClassType();
        }
        $type = $scope->getType($node);
        $type = $this->accessoryNonEmptyStringTypeCorrector->correct($type);
        $type = $this->genericClassStringTypeCorrector->correct($type);
        // hot fix for phpstan not resolving chain method calls
        if (!$node instanceof MethodCall) {
            return $type;
        }
        if (!$type instanceof MixedType) {
            return $type;
        }
        return $this->getType($node->var);
    }
    /**
     * e.g. string|null, ObjectNull|null
     */
    public function isNullableType(Node $node) : bool
    {
        $nodeType = $this->getType($node);
        return TypeCombinator::containsNull($nodeType);
    }
    public function getNativeType(Expr $expr) : Type
    {
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new MixedType();
        }
        $type = $scope->getNativeType($expr);
        return $this->accessoryNonEmptyStringTypeCorrector->correct($type);
    }
    public function isNumberType(Expr $expr) : bool
    {
        $nodeType = $this->getType($expr);
        if ($nodeType->isInteger()->yes()) {
            return \true;
        }
        return $nodeType->isFloat()->yes();
    }
    /**
     * @api
     * @param class-string<Type> $desiredType
     */
    public function isNullableTypeOfSpecificType(Node $node, string $desiredType) : bool
    {
        $nodeType = $this->getType($node);
        if (!$nodeType instanceof UnionType) {
            return \false;
        }
        if (!TypeCombinator::containsNull($nodeType)) {
            return \false;
        }
        $bareType = TypeCombinator::removeNull($nodeType);
        return $bareType instanceof $desiredType;
    }
    public function getFullyQualifiedClassName(TypeWithClassName $typeWithClassName) : string
    {
        if ($typeWithClassName instanceof ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        if ($typeWithClassName instanceof AliasedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        return $typeWithClassName->getClassName();
    }
    public function isMethodStaticCallOrClassMethodObjectType(Node $node, ObjectType $objectType) : bool
    {
        if ($node instanceof MethodCall) {
            // method call is variable return
            return $this->isObjectType($node->var, $objectType);
        }
        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $objectType);
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if ($classReflection->getName() === $objectType->getClassName()) {
            return \true;
        }
        return $classReflection->isSubclassOf($objectType->getClassName());
    }
    private function isUnionTypeable(Type $first, Type $second) : bool
    {
        return !$first instanceof UnionType && !$second instanceof UnionType && !$second instanceof NullType;
    }
    private function isMatchingUnionType(Type $resolvedType, ObjectType $requiredObjectType) : bool
    {
        $type = TypeCombinator::removeNull($resolvedType);
        // for falsy nullables
        $type = TypeCombinator::remove($type, new ConstantBooleanType(\false));
        if (!$type instanceof ObjectType) {
            return \false;
        }
        return $type->isInstanceOf($requiredObjectType->getClassName())->yes();
    }
    private function resolveByNodeTypeResolvers(Node $node) : ?Type
    {
        foreach ($this->nodeTypeResolvers as $nodeClass => $nodeTypeResolver) {
            if (!$node instanceof $nodeClass) {
                continue;
            }
            return $nodeTypeResolver->resolve($node);
        }
        return null;
    }
    private function isObjectTypeOfObjectType(ObjectType $resolvedObjectType, ObjectType $requiredObjectType) : bool
    {
        $requiredClassName = $requiredObjectType->getClassName();
        $resolvedClassName = $resolvedObjectType->getClassName();
        if ($resolvedClassName === $requiredClassName) {
            return \true;
        }
        if ($resolvedObjectType->isInstanceOf($requiredClassName)->yes()) {
            return \true;
        }
        if (!$this->reflectionProvider->hasClass($requiredClassName)) {
            return \false;
        }
        $requiredClassReflection = $this->reflectionProvider->getClass($requiredClassName);
        if ($requiredClassReflection->isTrait()) {
            if (!$this->reflectionProvider->hasClass($resolvedClassName)) {
                return \false;
            }
            $resolvedClassReflection = $this->reflectionProvider->getClass($resolvedClassName);
            foreach ($resolvedClassReflection->getAncestors() as $ancestorClassReflection) {
                if ($ancestorClassReflection->hasTraitUse($requiredClassName)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    private function resolveObjectType(ObjectType $resolvedObjectType, ObjectType $requiredObjectType) : bool
    {
        $renamedObjectType = $this->renamedClassesDataCollector->matchClassName($resolvedObjectType);
        if (!$renamedObjectType instanceof ObjectType) {
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
    private function resolveTernaryType(Ternary $ternary)
    {
        if ($ternary->if instanceof Expr) {
            $first = $this->getType($ternary->if);
            $second = $this->getType($ternary->else);
            if ($this->isUnionTypeable($first, $second)) {
                return new UnionType([$first, $second]);
            }
        }
        $condType = $this->getType($ternary->cond);
        if ($this->isNullableType($ternary->cond) && $condType instanceof UnionType) {
            $first = $condType->getTypes()[0];
            $second = $this->getType($ternary->else);
            if ($this->isUnionTypeable($first, $second)) {
                return new UnionType([$first, $second]);
            }
        }
        return new MixedType();
    }
}
