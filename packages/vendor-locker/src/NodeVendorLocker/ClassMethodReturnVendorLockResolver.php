<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodReturnVendorLockResolver extends AbstractNodeVendorLockResolver
{
    public function isVendorLocked(ClassMethod $classMethod): bool
    {
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof ClassLike) {
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
        $parentClass = $this->nodeRepository->findClass($parentClassName);
        if ($parentClass !== null) {
            $parentClassMethod = $parentClass->getMethod($methodName);
            // validate type is conflicting
            // parent class method in local scope → it's ok
            if ($parentClassMethod !== null) {
                return $parentClassMethod->returnType !== null;
            }

            // if not, look for it's parent parent
        }

        // validate type is conflicting
        // parent class method in external scope → it's not ok
        return method_exists($parentClassName, $methodName);
    }
}
