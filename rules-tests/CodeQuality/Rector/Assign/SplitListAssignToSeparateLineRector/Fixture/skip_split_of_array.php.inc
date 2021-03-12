<?php

namespace Rector\Tests\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector\Fixture;

final class SkipSplitOfArray
{
    public function run(): void
    {
        $moreItems = [1, 2];
        [$a, $b] = $moreItems;
    }
}
