<?php

namespace Rector\Tests\Defluent\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

use Rector\Tests\Defluent\Rector\ClassMethod\ReturnThisRemoveRector\Source\vendor\ParentInVendor;

class SkipParentInVendor extends ParentInVendor
{
    /**
     * @return SkipParentInVendor
     */
    public function someFunction()
    {
        foo();
        return $this;
    }
}
