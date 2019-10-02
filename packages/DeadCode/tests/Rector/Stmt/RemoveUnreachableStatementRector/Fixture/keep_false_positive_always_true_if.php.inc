<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class KeepFalsePositiveAlwaysTrueIf
{
    public function run(int $value)
    {
        $value = 'one';

        if (is_int($value)) {
            return 5;
        }

        $removeMe = 10;
        return $removeMe;
    }
}
