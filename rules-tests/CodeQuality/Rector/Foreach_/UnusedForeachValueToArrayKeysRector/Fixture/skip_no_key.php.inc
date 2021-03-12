<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

class SkipNoKey
{
    public function run()
    {
        $items = [];
        foreach ($values as $value) {
            $items[$value] = null;
        }
    }
}
