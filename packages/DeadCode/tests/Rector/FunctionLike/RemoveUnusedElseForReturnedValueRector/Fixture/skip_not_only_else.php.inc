<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveUnusedElseForReturnedValueRector\Fixture;

class SkipNotOnlyElse
{
    public function run($value)
    {
        if ($value) {
            return 5;
        } elseif ($value- 1) {
            $value = 55;
            return;
        } else {
            return 10;
        }
    }

    public function runAgain($value)
    {
        if ($value) {
            return 5;
        } elseif ($value- 1) {
            $value = 55;
            return 10;
        } else {
            return;
        }
    }

    public function runAgainAndAgain($value)
    {
        if ($value) {
            5 + 10;
        } else {
            return $value;
        }
    }
}
