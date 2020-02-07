<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class SkipTypeChnge
{
    public function runBinary()
    {
        $value = '5';

        return $value + 0;
    }

    public function runAssign()
    {
        $value = '5';
        $value += 0;

        return $value;
    }
}
