<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\VendorLocker;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;
use RectorPrefix20220606\Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use RectorPrefix20220606\Rector\VendorLocker\NodeVendorLocker\PropertyTypeVendorLockResolver;
final class VendorLockResolver
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver
     */
    private $classMethodParamVendorLockResolver;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver
     */
    private $classMethodReturnVendorLockResolver;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\PropertyTypeVendorLockResolver
     */
    private $propertyTypeVendorLockResolver;
    public function __construct(ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver, ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver, PropertyTypeVendorLockResolver $propertyTypeVendorLockResolver)
    {
        $this->classMethodParamVendorLockResolver = $classMethodParamVendorLockResolver;
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
        $this->propertyTypeVendorLockResolver = $propertyTypeVendorLockResolver;
    }
    public function isClassMethodParamLockedIn(Node $node) : bool
    {
        if (!$node instanceof ClassMethod) {
            return \false;
        }
        return $this->classMethodParamVendorLockResolver->isVendorLocked($node);
    }
    public function isReturnChangeVendorLockedIn(ClassMethod $classMethod) : bool
    {
        return $this->classMethodReturnVendorLockResolver->isVendorLocked($classMethod);
    }
    public function isPropertyTypeChangeVendorLockedIn(Property $property) : bool
    {
        return $this->propertyTypeVendorLockResolver->isVendorLocked($property);
    }
}
