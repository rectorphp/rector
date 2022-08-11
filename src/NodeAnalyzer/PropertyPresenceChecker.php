<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Rector\PostRector\ValueObject\PropertyMetadata;
/**
 * Can be local property, parent property etc.
 */
final class PropertyPresenceChecker
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver
     */
    private $promotedPropertyResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(PromotedPropertyResolver $promotedPropertyResolver, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, AstResolver $astResolver)
    {
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->astResolver = $astResolver;
    }
    /**
     * Includes parent classes and traits
     */
    public function hasClassContextProperty(Class_ $class, PropertyMetadata $propertyMetadata) : bool
    {
        $propertyOrParam = $this->getClassContextProperty($class, $propertyMetadata);
        return $propertyOrParam !== null;
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|null
     */
    public function getClassContextProperty(Class_ $class, PropertyMetadata $propertyMetadata)
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $property = $class->getProperty($propertyMetadata->getName());
        if ($property instanceof Property) {
            return $property;
        }
        $property = $this->matchPropertyByParentNonPrivateProperties($className, $propertyMetadata);
        if ($property instanceof Property || $property instanceof Param) {
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
     * @return PhpPropertyReflection[]
     */
    private function getParentClassNonPrivatePropertyReflections(string $className) : array
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $propertyReflections = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $propertyNames = $this->resolveNonPrivatePropertyNames($parentClassReflection);
            foreach ($propertyNames as $propertyName) {
                $propertyReflections[] = $parentClassReflection->getNativeProperty($propertyName);
            }
        }
        return $propertyReflections;
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|null
     */
    private function matchPropertyByType(PropertyMetadata $propertyMetadata, PhpPropertyReflection $phpPropertyReflection)
    {
        if ($propertyMetadata->getType() === null) {
            return null;
        }
        if (!$propertyMetadata->getType() instanceof TypeWithClassName) {
            return null;
        }
        if (!$phpPropertyReflection->getWritableType() instanceof TypeWithClassName) {
            return null;
        }
        $propertyObjectType = $propertyMetadata->getType();
        if (!$propertyObjectType->equals($phpPropertyReflection->getWritableType())) {
            return null;
        }
        return $this->astResolver->resolvePropertyFromPropertyReflection($phpPropertyReflection);
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|null
     */
    private function matchPropertyByParentNonPrivateProperties(string $className, PropertyMetadata $propertyMetadata)
    {
        $availablePropertyReflections = $this->getParentClassNonPrivatePropertyReflections($className);
        foreach ($availablePropertyReflections as $availablePropertyReflection) {
            // 1. match type by priority
            $property = $this->matchPropertyByType($propertyMetadata, $availablePropertyReflection);
            if ($property instanceof Property || $property instanceof Param) {
                return $property;
            }
            $nativePropertyReflection = $availablePropertyReflection->getNativeReflection();
            // 2. match by name
            if ($nativePropertyReflection->getName() === $propertyMetadata->getName()) {
                return $this->astResolver->resolvePropertyFromPropertyReflection($availablePropertyReflection);
            }
        }
        return null;
    }
    /**
     * @return string[]
     */
    private function resolveNonPrivatePropertyNames(ClassReflection $classReflection) : array
    {
        $propertyNames = [];
        $nativeReflection = $classReflection->getNativeReflection();
        foreach ($nativeReflection->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->isPrivate()) {
                continue;
            }
            $propertyNames[] = $reflectionProperty->getName();
        }
        return $propertyNames;
    }
}
