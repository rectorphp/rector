<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;

/**
 * @deprecated
 * Merge with @see ClassMethodVisibilityGuard
 */
final class ClassMethodVisibilityVendorLockResolver extends AbstractNodeVendorLockResolver
{
    /**
     * Checks for:
     * - interface required methods
     * - abstract classes required method
     * - child classes required method
     *
     * Prevents:
     * - changing visibility conflicting with children
     */
    public function isParentLockedMethod(ClassMethod $classMethod): bool
    {
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        if ($this->isInterfaceMethod($classMethod, $className)) {
            return true;
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        return $this->hasParentMethod($className, $methodName);
    }

    public function isChildLockedMethod(ClassMethod $classMethod): bool
    {
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        return $this->hasChildMethod($className, $methodName);
    }

    private function isInterfaceMethod(ClassMethod $classMethod, string $className): bool
    {
        $interfaceMethodNames = $this->getInterfaceMethodNames($className);
        return $this->nodeNameResolver->isNames($classMethod, $interfaceMethodNames);
    }

    private function hasParentMethod(string $className, string $methodName): bool
    {
        /** @var string[] $parentClasses */
        $parentClasses = (array) class_parents($className);

        foreach ($parentClasses as $parentClass) {
            if (! method_exists($parentClass, $methodName)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function hasChildMethod(string $desiredClassName, string $methodName): bool
    {
        foreach (get_declared_classes() as $className) {
            if ($className === $desiredClassName) {
                continue;
            }

            if (! is_a($className, $desiredClassName, true)) {
                continue;
            }

            if (method_exists($className, $methodName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getInterfaceMethodNames(string $className): array
    {
        /** @var string[] $interfaces */
        $interfaces = (array) class_implements($className);

        $interfaceMethods = [];
        foreach ($interfaces as $interface) {
            $currentInterfaceMethods = get_class_methods($interface);
            $interfaceMethods = array_merge($interfaceMethods, $currentInterfaceMethods);
        }

        return $interfaceMethods;
    }
}
