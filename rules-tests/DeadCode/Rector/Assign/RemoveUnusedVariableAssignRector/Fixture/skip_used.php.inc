<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipUsed
{
    public function run()
    {
        $value = 5;

        if (rand(0, 10)) {
            return $value;
        }
    }
}
