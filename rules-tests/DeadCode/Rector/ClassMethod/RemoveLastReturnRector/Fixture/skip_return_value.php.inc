<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveLastReturnRector\Fixture;

final class SkipReturnValue
{
    public function some_function($value)
    {
        if ($value === 1000) {
            return;
        }

        if ($value) {
            return 10;
        }
    }
}
