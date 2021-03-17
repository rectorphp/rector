<?php

namespace Rector\Tests\Php70\Rector\Assign\ListSwapArrayOrderRector\Fixture;

class SkipArrayDim
{
    public function a()
    {
        list($a['one'], $a['two']) = [1, 2];
    }
}
