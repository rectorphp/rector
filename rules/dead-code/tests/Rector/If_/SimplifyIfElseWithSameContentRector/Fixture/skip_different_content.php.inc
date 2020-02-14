<?php

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

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
