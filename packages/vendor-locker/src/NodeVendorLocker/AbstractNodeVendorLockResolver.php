<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;

abstract class AbstractNodeVendorLockResolver
{
    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @required
     */
    public function autowireAbstractNodeVendorLockResolver(
        NodeRepository $nodeRepository,
        NodeNameResolver $nodeNameResolver
    ): void {
        $this->nodeRepository = $nodeRepository;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    protected function hasParentClassChildrenClassesOrImplementsInterface(ClassReflection $classReflection): bool
    {
        if ($classReflection->isClass()) {
            if ($classReflection->getParents()) {
                return true;
            }

            if ($classReflection->getInterfaces() !== []) {
                return true;
            }

            return $classReflection->getAncestors() !== [$classReflection];
        }

        if (! $classReflection->isInterface()) {
            return false;
        }

        return $classReflection->getInterfaces() !== [];
    }

    protected function isMethodVendorLockedByInterface(ClassReflection $classReflection, string $methodName): bool
    {
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->hasMethod($methodName)) {
                return true;
            }
        }

        return false;
    }
}
