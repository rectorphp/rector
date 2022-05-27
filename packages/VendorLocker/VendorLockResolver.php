<?php

declare (strict_types=1);
namespace Rector\VendorLocker;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
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
    public function __construct(\Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver, \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver, \Rector\VendorLocker\NodeVendorLocker\PropertyTypeVendorLockResolver $propertyTypeVendorLockResolver)
    {
        $this->classMethodParamVendorLockResolver = $classMethodParamVendorLockResolver;
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
        $this->propertyTypeVendorLockResolver = $propertyTypeVendorLockResolver;
    }
    public function isClassMethodParamLockedIn(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        return $this->classMethodParamVendorLockResolver->isVendorLocked($node);
    }
    public function isReturnChangeVendorLockedIn(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        return $this->classMethodReturnVendorLockResolver->isVendorLocked($classMethod);
    }
    public function isPropertyTypeChangeVendorLockedIn(\PhpParser\Node\Stmt\Property $property) : bool
    {
        return $this->propertyTypeVendorLockResolver->isVendorLocked($property);
    }
}
