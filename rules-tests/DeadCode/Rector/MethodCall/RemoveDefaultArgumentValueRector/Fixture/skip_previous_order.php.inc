<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

class SkipPreviousOrder
{
    public function run()
    {
        $this->runWithDefault([], 5);
    }

    public function runWithDefault($items = [], $value)
    {
        return $items;
    }
}
