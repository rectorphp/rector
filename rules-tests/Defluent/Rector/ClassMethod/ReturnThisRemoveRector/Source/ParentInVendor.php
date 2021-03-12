<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Source;

use Rector\Defluent\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture\SkipParentInVendor;

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
