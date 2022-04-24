<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class SkipRemoveVariableOnNextReturnAfterBreakInCase
{
    public function run($a)
    {
        switch ($a)
        {
            case 'A':
                $x = 'a';
                break;
                return;
        }

        echo $x;
    }
}
