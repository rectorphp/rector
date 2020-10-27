<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRule\Fixture;

use Countable;

class SkipNotArray
{
    public function run(Countable $countable)
    {
        count($countable);
    }
}
