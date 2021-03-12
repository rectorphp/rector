<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SkipNotCount
{
    public function run(array $array)
    {
        empty($array);
    }
}

?>
