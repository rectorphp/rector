<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class SkipNotOnlyElse
{

    public function runAgainAndAgain($value)
    {
        if ($value) {
            5 + 10;
        } else {
            return $value;
        }
    }
}
