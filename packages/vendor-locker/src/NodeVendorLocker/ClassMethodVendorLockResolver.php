<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

final class ClassMethodVendorLockResolver extends AbstractNodeVendorLockResolver
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

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
        /** @var string $classMethodName */
        $classMethodName = $this->nodeNameResolver->getName($classMethod);

        /** @var Class_|Interface_|null $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return false;
        }

        if ($this->isMethodVendorLockedByInterface($classLike, $classMethodName)) {
            return true;
        }

        if ($classLike->extends === null) {
            return false;
        }

        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        /** @var string[] $classParents */
        $classParents = (array) class_parents($className);

        foreach ($classParents as $classParent) {
            if (! $this->reflectionProvider->hasClass($classParent)) {
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
