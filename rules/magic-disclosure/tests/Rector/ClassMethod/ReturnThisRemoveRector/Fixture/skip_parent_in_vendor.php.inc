<?php

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Source\ParentInVendor;

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
