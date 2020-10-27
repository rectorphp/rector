<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRule\Fixture;

class SkipNotCount
{
    public function run(array $array)
    {
        empty($array);
    }
}
