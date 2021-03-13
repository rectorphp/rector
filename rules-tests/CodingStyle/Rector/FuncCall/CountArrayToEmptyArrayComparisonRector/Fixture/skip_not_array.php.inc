<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

use Countable;

class SkipNotArray
{
    public function run(Countable $countable)
    {
        count($countable);
    }
}

?>
