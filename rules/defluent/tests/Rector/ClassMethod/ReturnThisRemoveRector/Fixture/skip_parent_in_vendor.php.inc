<?php

namespace Rector\Defluent\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

use Rector\Defluent\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Source\ParentInVendor;

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
