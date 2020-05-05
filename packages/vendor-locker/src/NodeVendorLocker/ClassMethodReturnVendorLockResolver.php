<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodReturnVendorLockResolver extends AbstractNodeVendorLockResolver
{
    public function isVendorLocked(ClassMethod $classMethod): bool
    {
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if (! $this->hasParentClassOrImplementsInterface($classNode)) {
            return false;
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        // @todo extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            return $this->isVendorLockedByParentClass($parentClassName, $methodName);
        }

        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_ && ! $classNode instanceof Interface_) {
            return false;
        }

        return $this->isMethodVendorLockedByInterface($classNode, $methodName);
    }

    private function isVendorLockedByParentClass(string $parentClassName, string $methodName): bool
    {
        $parentClassNode = $this->parsedNodeCollector->findClass($parentClassName);
        if ($parentClassNode !== null) {
            $parentMethodNode = $parentClassNode->getMethod($methodName);
            // validate type is conflicting
            // parent class method in local scope → it's ok
            if ($parentMethodNode !== null) {
                return $parentMethodNode->returnType !== null;
            }

            // if not, look for it's parent parent
        }

        if (method_exists($parentClassName, $methodName)) {
            // validate type is conflicting
            // parent class method in external scope → it's not ok
            return true;
        }

        return false;
    }
}
