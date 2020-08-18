<?php

namespace Rector\Php70\Tests\Rector\Assign\ListSwapArrayOrderRector\Fixture;

class Skip
{
    public function b()
    {
        list($a[], $b[]) = [1, 2];
    }

    public function c()
    {
        list($a, $b, $c) = [1, 2, 3];
    }
}
