<?php

declare(strict_types=1);

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
    private array $classes = [];

    public function __construct(
        private ClassAnalyzer $classAnalyzer,
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function collectClass(Class_ $class): void
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return;
        }

        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            throw new ShouldNotHappenException();
        }

        $this->classes[$className] = $class;
    }

    /**
     * @param class-string $className
     * @return Class_[]
     * @deprecated Use static reflection instead
     */
    public function findChildrenOfClass(string $className): array
    {
        $childrenClasses = [];

        // @todo refactor to reflection
        foreach ($this->classes as $class) {
            $currentClassName = $class->getAttribute(AttributeKey::CLASS_NAME);
            if ($currentClassName === null) {
                continue;
            }

            if (! $this->isChildOrEqualClassLike($className, $currentClassName)) {
                continue;
            }

            $childrenClasses[] = $class;
        }

        return $childrenClasses;
    }

    private function isChildOrEqualClassLike(string $desiredClass, string $currentClassName): bool
    {
        if (! $this->reflectionProvider->hasClass($desiredClass)) {
            return false;
        }

        if (! $this->reflectionProvider->hasClass($currentClassName)) {
            return false;
        }

        $desiredClassReflection = $this->reflectionProvider->getClass($desiredClass);
        $currentClassReflection = $this->reflectionProvider->getClass($currentClassName);

        if (! $currentClassReflection->isSubclassOf($desiredClassReflection->getName())) {
            return false;
        }

        return $currentClassName !== $desiredClass;
    }
}
