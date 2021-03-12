<?php

namespace Rector\Tests\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

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
