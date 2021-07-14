<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveLastReturnRector\Fixture;

final class SkipNotClassReturn
{
    function some_function($value)
    {
        if ($value === 1000) {
            return;
        }

        if ($value) {
            return;
        }

        $value = 1000;
    }
}
