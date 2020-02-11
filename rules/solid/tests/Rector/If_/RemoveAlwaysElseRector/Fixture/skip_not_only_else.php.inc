<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

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
