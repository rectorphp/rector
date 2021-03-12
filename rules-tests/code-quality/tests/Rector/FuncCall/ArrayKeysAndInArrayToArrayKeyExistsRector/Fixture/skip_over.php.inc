<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

class SkipOver
{
    public function run($values)
    {
        $keys = array_keys($values);
    }

    public function again($packageName, $values, $keys)
    {
        return in_array($packageName, $keys, true);
    }
}
