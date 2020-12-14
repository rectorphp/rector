<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

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
