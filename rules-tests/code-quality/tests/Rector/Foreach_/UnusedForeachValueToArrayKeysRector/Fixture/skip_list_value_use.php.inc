<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

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
