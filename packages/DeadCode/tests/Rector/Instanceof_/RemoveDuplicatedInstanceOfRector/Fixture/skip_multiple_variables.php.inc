<?php

namespace Rector\DeadCode\Tests\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector\Fixture;

class SkipMultipleVariables
{
    public function run($value, $value2)
    {
        return $value instanceof A || $value2 instanceof A;
    }
}
