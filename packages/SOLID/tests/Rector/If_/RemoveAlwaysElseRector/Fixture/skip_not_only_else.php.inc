<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

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

    public function runAgainAndAgain($value)
    {
        if ($value) {
            5 + 10;
        } else {
            return $value;
        }
    }
}
