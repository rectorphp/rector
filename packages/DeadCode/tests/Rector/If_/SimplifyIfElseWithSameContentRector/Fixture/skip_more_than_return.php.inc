<?php

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class SkipMoreThanReturn
{
    public function go()
    {
        if (true) {
            return 1;
        } else {
            $also = 5;
            return 1;
        }
    }
}
