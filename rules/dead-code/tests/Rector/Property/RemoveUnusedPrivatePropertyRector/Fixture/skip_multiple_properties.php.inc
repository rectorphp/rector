<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipMultipleProperties
{
    private $used, $unused;
    private $unused2, $unused3;

    public function getUsed()
    {
        return $this->used;
    }
}
