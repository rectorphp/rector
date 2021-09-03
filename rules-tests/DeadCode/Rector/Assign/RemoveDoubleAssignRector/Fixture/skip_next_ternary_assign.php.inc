<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleAssignRector\Fixture;

class SkipNextTernaryAssign
{
    public function run($check)
    {
        $exists = [];

        $check
            ? []
            : $exists = [rand(0,1)];

        return $exists;
    }
}
