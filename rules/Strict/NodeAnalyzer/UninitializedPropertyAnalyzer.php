<?php

declare (strict_types=1);
namespace Rector\Strict\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ThisType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\AstResolver;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
final class UninitializedPropertyAnalyzer
{
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private ConstructorAssignDetector $constructorAssignDetector;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(AstResolver $astResolver, NodeTypeResolver $nodeTypeResolver, ConstructorAssignDetector $constructorAssignDetector, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->astResolver = $astResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isUninitialized(Expr $expr): bool
    {
        if (!$expr instanceof PropertyFetch && !$expr instanceof StaticPropertyFetch) {
            return \false;
        }
        $varType = $expr instanceof PropertyFetch ? $this->nodeTypeResolver->getType($expr->var) : $this->nodeTypeResolver->getType($expr->class);
        if ($varType instanceof ThisType) {
            $varType = $varType->getStaticObjectType();
        }
        $className = ClassNameFromObjectTypeResolver::resolve($varType);
        if ($className === null) {
            return \false;
        }
        $propertyName = (string) $this->nodeNameResolver->getName($expr);
        // fast-path: a typed property with an explicit default is always initialized,
        // so we can skip the heavy class parsing below
        if ($this->hasTypedDefaultProperty($className, $propertyName)) {
            return \false;
        }
        $classLike = $this->astResolver->resolveClassFromName($className);
        if (!$classLike instanceof ClassLike) {
            return \false;
        }
        $property = $classLike->getProperty($propertyName);
        if (!$property instanceof Property) {
            return \false;
        }
        if (count($property->props) !== 1) {
            return \false;
        }
        if ($property->props[0]->default instanceof Expr) {
            return \false;
        }
        return !$this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName);
    }
    private function hasTypedDefaultProperty(string $className, string $propertyName): bool
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->hasNativeProperty($propertyName)) {
            return \false;
        }
        $nativeReflectionProperty = $classReflection->getNativeProperty($propertyName)->getNativeReflection();
        // an untyped property has an implicit null default, which is not an explicit initialization
        return $nativeReflectionProperty->hasType() && $nativeReflectionProperty->hasDefaultValue();
    }
}
