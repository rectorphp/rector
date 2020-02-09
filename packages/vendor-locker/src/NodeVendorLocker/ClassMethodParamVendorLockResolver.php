<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodParamVendorLockResolver extends AbstractNodeVendorLockResolver
{
    public function isVendorLocked(ClassMethod $classMethod, int $paramPosition): bool
    {
        /** @var Class_|null $classNode */
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if (! $this->hasParentClassOrImplementsInterface($classNode)) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);
        if (! is_string($methodName)) {
            throw new ShouldNotHappenException();
        }

        // @todo extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            $vendorLock = $this->isParentClassVendorLocking($paramPosition, $parentClassName, $methodName);
            if ($vendorLock !== null) {
                return $vendorLock;
            }
        }

        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_ && ! $classNode instanceof Interface_) {
            return false;
        }

        $interfaceNames = $this->classManipulator->getClassLikeNodeParentInterfaceNames($classNode);
        return $this->isInterfaceParamVendorLockin($interfaceNames, $methodName);
    }

    private function isParentClassVendorLocking(int $paramPosition, string $parentClassName, string $methodName): ?bool
    {
        $parentClassNode = $this->parsedNodeCollector->findClass($parentClassName);
        if ($parentClassNode !== null) {
            $parentMethodNode = $parentClassNode->getMethod($methodName);
            // @todo validate type is conflicting
            // parent class method in local scope → it's ok
            if ($parentMethodNode !== null) {
                // parent method has no type → we cannot change it here
                return isset($parentMethodNode->params[$paramPosition]) && $parentMethodNode->params[$paramPosition]->type === null;
            }
        }

        // if not, look for it's parent parent - @todo recursion

        if (method_exists($parentClassName, $methodName)) {
            // @todo validate type is conflicting
            // parent class method in external scope → it's not ok
            return true;

            // if not, look for it's parent parent - @todo recursion
        }

        return null;
    }

    private function isInterfaceParamVendorLockin(array $interfaceNames, string $methodName): bool
    {
        foreach ($interfaceNames as $interfaceName) {
            $interface = $this->parsedNodeCollector->findInterface($interfaceName);
            // parent class method in local scope → it's ok
            // @todo validate type is conflicting
            if ($interface !== null && $interface->getMethod($methodName) !== null) {
                return false;
            }

            if (method_exists($interfaceName, $methodName)) {
                // parent class method in external scope → it's not ok
                // @todo validate type is conflicting
                return true;
            }
        }

        return false;
    }
}
