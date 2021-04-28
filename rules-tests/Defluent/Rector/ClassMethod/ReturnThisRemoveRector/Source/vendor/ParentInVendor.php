<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\ClassMethod\ReturnThisRemoveRector\Source\vendor;

class ParentInVendor
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
