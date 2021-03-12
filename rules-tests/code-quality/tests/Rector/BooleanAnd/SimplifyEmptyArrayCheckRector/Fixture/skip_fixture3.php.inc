<?php

namespace Rector\CodeQuality\Tests\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector\Fixture;

class SkipFixture3
{
    public function isntEmptyArray($values): bool
    {
        return is_array($values) && !empty($values);
    }

    public function isntEmptyArray2($values): bool
    {
        return !empty($values) && is_array($values);
    }

    public function twoNegatives($values): bool
    {
        return !empty($values->foo) && !is_array($values->bar);
    }

}

function simplifyEmptyCheck2()
{
    $objects = [];
    is_array($objects) && is_array($objects);
}
