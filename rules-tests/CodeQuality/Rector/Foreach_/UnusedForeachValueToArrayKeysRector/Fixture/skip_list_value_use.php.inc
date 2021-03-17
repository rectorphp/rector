<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

class SkipListValueUse
{
    public function run(array $definitions)
    {
        foreach ($definitions as $id => [$domElement, $file]) {
            if ($domElement && $file) {
            }
        }
    }
}
