<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleAssignRector\Fixture;

class SkipNextCoalesceAssign
{
    public function run($check = null)
    {
        $exists = [];

        $check ?? $exists = [rand(0,1)];

        return $exists;
    }
}
