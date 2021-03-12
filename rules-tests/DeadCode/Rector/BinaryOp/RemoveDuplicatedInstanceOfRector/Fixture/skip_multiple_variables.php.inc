<?php

namespace Rector\Tests\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector\Fixture;

class SkipMultipleVariables
{
    public function run($value, $value2)
    {
        return $value instanceof A || $value2 instanceof A;
    }
}
