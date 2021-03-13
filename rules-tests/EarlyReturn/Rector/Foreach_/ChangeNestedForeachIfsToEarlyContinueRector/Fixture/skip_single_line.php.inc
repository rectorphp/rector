<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class SkipSingleLine
{
    public function run()
    {
        $items = [];

        foreach ($values as $value) {
            if ($value === 5) {
                $items[] = 'maybe';
            }
        }
    }
}
