<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SkipOnlyIf
{
    public function run($a)
    {
        if ($a) {
            return 1;
        }

        echo 'executable';
    }
}
