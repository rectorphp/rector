<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SkipNotCount
{
    public function run(array $array)
    {
        empty($array);
    }
}

?>