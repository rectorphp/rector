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

        $parentClasses = $this->getParentClasses($class);
        $propertyName = $this->nodeNameResolver->getName($classMethod);

        foreach ($parentClasses as $parentClass) {
            if (method_exists($parentClass, $propertyName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return class-string[]
     */
    private function getParentClasses(Class_ $class): array
    {
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);

        return class_parents($className);
    }
}
