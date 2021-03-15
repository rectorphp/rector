<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Source\Collection;

class SkipValuesIsObject
{
    public function run()
    {
        $collection = new Collection();
        foreach ($collection as $key => $value) {
            $collection[$key] = null;
        }
    }
}

?>
