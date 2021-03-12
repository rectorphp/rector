<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class SkipFloats
{
    public function run()
    {
        $value = 5;
        var_dump($value); // 5

        $value = $value * 1.0;
        var_dump($value); // 5.0
    }
}
