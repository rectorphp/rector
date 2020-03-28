<?php

declare(strict_types=1);

namespace Rector\VendorLocker;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodVendorLockResolver;
use Rector\VendorLocker\NodeVendorLocker\PropertyVendorLockResolver;

final class VendorLockResolver
{
    /**
     * @var ClassMethodReturnVendorLockResolver
     */
    private $classMethodReturnVendorLockResolver;

    /**
     * @var ClassMethodParamVendorLockResolver
     */
    private $classMethodParamVendorLockResolver;

    /**
     * @var PropertyVendorLockResolver
     */
    private $propertyVendorLockResolver;

    /**
     * @var ClassMethodVendorLockResolver
     */
    private $classMethodVendorLockResolver;

    public function __construct(
        ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver,
        ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver,
        PropertyVendorLockResolver $propertyVendorLockResolver,
        ClassMethodVendorLockResolver $classMethodVendorLockResolver
    ) {
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
        $this->classMethodParamVendorLockResolver = $classMethodParamVendorLockResolver;
        $this->propertyVendorLockResolver = $propertyVendorLockResolver;
        $this->classMethodVendorLockResolver = $classMethodVendorLockResolver;
    }

    public function isClassMethodParamLockedIn(Node $node, int $paramPosition): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        return $this->isParamChangeVendorLockedIn($node, $paramPosition);
    }

    public function isParamChangeVendorLockedIn(ClassMethod $classMethod, int $paramPosition): bool
    {
        return $this->classMethodParamVendorLockResolver->isVendorLocked($classMethod, $paramPosition);
    }

    public function isReturnChangeVendorLockedIn(ClassMethod $classMethod): bool
    {
        return $this->classMethodReturnVendorLockResolver->isVendorLocked($classMethod);
    }

    public function isPropertyChangeVendorLockedIn(Property $property): bool
    {
        return $this->propertyVendorLockResolver->isVendorLocked($property);
    }

    public function isClassMethodRemovalVendorLocked(ClassMethod $classMethod): bool
    {
        return $this->classMethodVendorLockResolver->isRemovalVendorLocked($classMethod);
    }
}
