<?php

declare(strict_types=1);

namespace Rector\VendorLocker;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodVendorLockResolver;
use Rector\VendorLocker\NodeVendorLocker\PropertyTypeVendorLockResolver;

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
     * @var PropertyTypeVendorLockResolver
     */
    private $propertyTypeVendorLockResolver;

    /**
     * @var ClassMethodVendorLockResolver
     */
    private $classMethodVendorLockResolver;

    public function __construct(
        ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver,
        ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver,
        ClassMethodVendorLockResolver $classMethodVendorLockResolver,
        PropertyTypeVendorLockResolver $propertyTypeVendorLockResolver
    ) {
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
        $this->classMethodParamVendorLockResolver = $classMethodParamVendorLockResolver;
        $this->propertyTypeVendorLockResolver = $propertyTypeVendorLockResolver;
        $this->classMethodVendorLockResolver = $classMethodVendorLockResolver;
    }

    public function isClassMethodParamLockedIn(Node $node, int $paramPosition): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        return $this->classMethodParamVendorLockResolver->isVendorLocked($node, $paramPosition);
    }

    public function isReturnChangeVendorLockedIn(ClassMethod $classMethod): bool
    {
        return $this->classMethodReturnVendorLockResolver->isVendorLocked($classMethod);
    }

    public function isPropertyTypeChangeVendorLockedIn(Property $property): bool
    {
        return $this->propertyTypeVendorLockResolver->isVendorLocked($property);
    }

    public function isClassMethodRemovalVendorLocked(ClassMethod $classMethod): bool
    {
        return $this->classMethodVendorLockResolver->isRemovalVendorLocked($classMethod);
    }
}
