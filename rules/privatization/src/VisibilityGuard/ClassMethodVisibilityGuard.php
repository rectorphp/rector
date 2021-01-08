<?php

declare(strict_types=1);

namespace Rector\Privatization\VisibilityGuard;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassMethodVisibilityGuard
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isClassMethodVisibilityGuardedByParent(ClassMethod $classMethod, Class_ $class): bool
    {
        if ($class->extends === null) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);
        $parentClasses = $this->getParentClasses($class);
        return $this->methodExistsInClasses($parentClasses, $methodName);
    }

    public function isClassMethodVisibilityGuardedByTrait(ClassMethod $classMethod, Class_ $class): bool
    {
        $traits = $this->getParentTraits($class);
        $methodName = $this->nodeNameResolver->getName($classMethod);

        return $this->methodExistsInClasses($traits, $methodName);
    }

    /**
     * @return string[]
     */
    public function getParentTraits(Class_ $class): array
    {
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);

        $traits = class_uses($className);
        if ($traits === false) {
            return [];
        }

        return $traits;
    }

    /**
     * @return string[]
     */
    private function getParentClasses(Class_ $class): array
    {
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);

        $classParents = class_parents($className);
        if ($classParents === false) {
            return [];
        }

        return $classParents;
    }

    /**
     * @param string[] $classes
     */
    private function methodExistsInClasses(array $classes, string $method): bool
    {
        foreach ($classes as $class) {
            if (method_exists($class, $method)) {
                return true;
            }
        }

        return false;
    }
}
