<?php

namespace Rector\Tests\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector\Fixture;

class SkipArraySpreadIntKey
{
    public function run()
    {
        $parts = ['a'];
        $parts2 = ['c'];

        $result = [...$parts, ...$parts2];
    }
}
