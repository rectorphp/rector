<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
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

        if (! $this->hasParentClassChildrenClassesOrImplementsInterface($classNode)) {
            return false;
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

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

        return $this->isMethodVendorLockedByInterface($classNode, $methodName);
    }

    private function isParentClassVendorLocking(int $paramPosition, string $parentClassName, string $methodName): ?bool
    {
        $parentClassNode = $this->parsedNodeCollector->findClass($parentClassName);
        if ($parentClassNode !== null) {
            $parentMethodNode = $parentClassNode->getMethod($methodName);
            // parent class method in local scope → it's ok
            if ($parentMethodNode !== null) {
                // parent method has no type → we cannot change it here
                return isset($parentMethodNode->params[$paramPosition]) && $parentMethodNode->params[$paramPosition]->type === null;
            }
        }

        // if not, look for it's parent parent
        if (method_exists($parentClassName, $methodName)) {
            // parent class method in external scope → it's not ok
            // if not, look for it's parent parent
            return true;
        }

        return null;
    }
}
