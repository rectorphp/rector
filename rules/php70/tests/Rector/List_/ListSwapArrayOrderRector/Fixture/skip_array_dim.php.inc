<?php

namespace Rector\Php70\Tests\Rector\List_\ListSwapArrayOrderRector\Fixture;

class SkipArrayDim
{
    public function a()
    {
        list($a['one'], $a['two']) = [1, 2];
    }
}
