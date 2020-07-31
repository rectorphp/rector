<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Source;

use Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture\SkipParentInVendor;

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
