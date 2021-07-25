<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Rector\PostRector\ValueObject\PropertyMetadata;
use ReflectionNamedType;
use ReflectionProperty;
/**
 * Can be local property, parent property etc.
 */
final class PropertyPresenceChecker
{
    /**
     * @var \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver
     */
    private $promotedPropertyResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\Php80\NodeAnalyzer\PromotedPropertyResolver $promotedPropertyResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->astResolver = $astResolver;
    }
    /**
     * Includes parent classes and traits
     */
    public function hasClassContextProperty(\PhpParser\Node\Stmt\Class_ $class, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata) : bool
    {
        $propertyOrParam = $this->getClassContextProperty($class, $propertyMetadata);
        return $propertyOrParam !== null;
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|null
     */
    public function getClassContextProperty(\PhpParser\Node\Stmt\Class_ $class, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata)
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $property = $class->getProperty($propertyMetadata->getName());
        if ($property instanceof \PhpParser\Node\Stmt\Property) {
            return $property;
        }
        $property = $this->matchPropertyByParentPublicOrProtectedProperties($className, $propertyMetadata);
        if ($property instanceof \PhpParser\Node\Stmt\Property || $property instanceof \PhpParser\Node\Param) {
            return $property;
        }
        $promotedPropertyParams = $this->promotedPropertyResolver->resolveFromClass($class);
        foreach ($promotedPropertyParams as $promotedPropertyParam) {
            if ($this->nodeNameResolver->isName($promotedPropertyParam, $propertyMetadata->getName())) {
                return $promotedPropertyParam;
            }
        }
        return null;
    }
    /**
     * @return ReflectionProperty[]
     */
    private function getParentClassPublicAndProtectedPropertyReflections(string $className) : array
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $propertyReflections = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $nativeReflectionClass = $parentClassReflection->getNativeReflection();
            $currentPropertyReflections = $nativeReflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
            $propertyReflections = \array_merge($propertyReflections, $currentPropertyReflections);
        }
        return $propertyReflections;
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|null
     */
    private function matchPropertyByType(\Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata, \ReflectionProperty $reflectionProperty)
    {
        if ($propertyMetadata->getType() === null) {
            return null;
        }
        if (!$reflectionProperty->getType() instanceof \ReflectionNamedType) {
            return null;
        }
        if (!$propertyMetadata->getType() instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $propertyObjectType = $propertyMetadata->getType();
        $propertyObjectTypeClassName = $propertyObjectType->getClassName();
        if ($propertyObjectTypeClassName !== (string) $reflectionProperty->getType()) {
            return null;
        }
        $propertyObjectType = $propertyMetadata->getType();
        $propertyObjectTypeClassName = $propertyObjectType->getClassName();
        if ($propertyObjectTypeClassName !== (string) $reflectionProperty->getType()) {
            return null;
        }
        return $this->astResolver->resolvePropertyFromPropertyReflection($reflectionProperty);
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|null
     */
    private function matchPropertyByParentPublicOrProtectedProperties(string $className, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata)
    {
        $availablePropertyReflections = $this->getParentClassPublicAndProtectedPropertyReflections($className);
        foreach ($availablePropertyReflections as $availablePropertyReflection) {
            // 1. match type by priority
            $property = $this->matchPropertyByType($propertyMetadata, $availablePropertyReflection);
            if ($property instanceof \PhpParser\Node\Stmt\Property || $property instanceof \PhpParser\Node\Param) {
                return $property;
            }
            // 2. match by name
            if ($availablePropertyReflection->getName() === $propertyMetadata->getName()) {
                return $this->astResolver->resolvePropertyFromPropertyReflection($availablePropertyReflection);
            }
        }
        return null;
    }
}
