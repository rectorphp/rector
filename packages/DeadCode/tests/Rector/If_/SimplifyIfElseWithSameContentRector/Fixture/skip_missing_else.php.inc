<?php

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class SkipMissingElse
{
    public function go()
    {
        if (true) {
            return 1;
        } elseif (false) {
            return 1;
        }
    }
}
