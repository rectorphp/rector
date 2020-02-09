<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

final class ClassMethodVendorLockResolver extends AbstractNodeVendorLockResolver
{
    /**
     * Checks for:
     * - interface required methods
     * - abstract classes reqired method
     *
     * Prevent:
     * - removing class methods, that breaks the code
     */
    public function isRemovalVendorLocked(ClassMethod $classMethod): bool
    {
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        if (! is_string($classMethodName)) {
            throw new ShouldNotHappenException();
        }

        /** @var Class_|null $class */
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return false;
        }

        if ($this->isMethodVendorLockedByInterface($class, $classMethodName)) {
            return true;
        }

        if ($class->extends === null) {
            return false;
        }

        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        $classParents = class_parents($className);
        foreach ($classParents as $classParent) {
            if (! class_exists($classParent)) {
                continue;
            }

            $parentClassReflection = new ReflectionClass($classParent);
            if (! $parentClassReflection->hasMethod($classMethodName)) {
                continue;
            }

            $methodReflection = $parentClassReflection->getMethod($classMethodName);
            if (! $methodReflection->isAbstract()) {
                continue;
            }

            return true;
        }

        return false;
    }
}
