<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeCollector;

use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * This service contains all the parsed nodes. E.g. all the functions, method call, classes, static calls etc. It's
 * useful in case of context analysis, e.g. find all the usage of class method to detect, if the method is used.
 *
 * @deprecated
 */
final class NodeRepository
{
    /**
     * @deprecated Not reliable, as only works with so-far parsed classes
     * @var Class_[]
     */
    private $classes = [];
    /**
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function collectClass(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return;
        }
        $className = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $this->classes[$className] = $class;
    }
    /**
     * @param class-string $className
     * @return Class_[]
     * @deprecated Use static reflection instead
     */
    public function findChildrenOfClass(string $className) : array
    {
        $childrenClasses = [];
        // @todo refactor to reflection
        foreach ($this->classes as $class) {
            $currentClassName = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            if ($currentClassName === null) {
                continue;
            }
            if (!$this->isChildOrEqualClassLike($className, $currentClassName)) {
                continue;
            }
            $childrenClasses[] = $class;
        }
        return $childrenClasses;
    }
    private function isChildOrEqualClassLike(string $desiredClass, string $currentClassName) : bool
    {
        if (!$this->reflectionProvider->hasClass($desiredClass)) {
            return \false;
        }
        if (!$this->reflectionProvider->hasClass($currentClassName)) {
            return \false;
        }
        $desiredClassReflection = $this->reflectionProvider->getClass($desiredClass);
        $currentClassReflection = $this->reflectionProvider->getClass($currentClassName);
        if (!$currentClassReflection->isSubclassOf($desiredClassReflection->getName())) {
            return \false;
        }
        return $currentClassName !== $desiredClass;
    }
}
