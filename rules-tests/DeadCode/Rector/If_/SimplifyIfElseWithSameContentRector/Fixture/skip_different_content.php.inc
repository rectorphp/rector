<?php

namespace Rector\Tests\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class SkipDifferentContent
{
    public function run()
    {
        if (true) {
            return 1;
        } else {
            return 2;
        }
    }
}
