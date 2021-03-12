<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

use Countable;

class SkipNotArray
{
    public function run(Countable $countable)
    {
        count($countable);
    }
}

?>
