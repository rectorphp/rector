<?php

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class SkipElseWithNoReturn
{
    public function go()
    {
        if (true) {
            return 1;
        } else {
            $value = 10;
        }
    }
}
