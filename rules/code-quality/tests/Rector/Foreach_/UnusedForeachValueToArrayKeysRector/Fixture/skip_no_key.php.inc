<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

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
