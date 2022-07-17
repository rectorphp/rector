<?php

declare (strict_types=1);
namespace Rector\VendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Rector\VendorLocker\NodeVendorLocker\PropertyTypeVendorLockResolver;
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
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    public function isClassMethodParamLockedIn($node) : bool
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
