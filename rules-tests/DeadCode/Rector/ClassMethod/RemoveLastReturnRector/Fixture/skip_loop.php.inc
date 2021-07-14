<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveLastReturnRector\Fixture;

final class SkipLoop
{
    function some_function($value, array $items)
    {
        if ($value === 1000) {
            return;
        }

        foreach ($items as $item) {
            if ($item == 100) {
                $this->process();
                return;
            }
        }
    }

    private function process()
    {
    }
}
