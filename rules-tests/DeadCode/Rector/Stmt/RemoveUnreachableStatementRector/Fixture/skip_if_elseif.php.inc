<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SkipIfElseIf
{
    public function run($a, $b)
    {
        if ($a) {
            return 1;
        } elseif ($b) {
            return 2;
        }

        echo 'executable';
    }
}
