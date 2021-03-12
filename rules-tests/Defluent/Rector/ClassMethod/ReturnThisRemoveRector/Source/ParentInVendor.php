<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\ClassMethod\ReturnThisRemoveRector\Source;

use Rector\Tests\Defluent\Rector\ClassMethod\ReturnThisRemoveRector\Fixture\SkipParentInVendor;

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
