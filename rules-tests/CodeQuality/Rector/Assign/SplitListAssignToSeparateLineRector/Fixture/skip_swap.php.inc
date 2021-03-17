<?php

namespace Rector\Tests\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector\Fixture;

final class SkipSwap
{
    public function run(): void
    {
        $a = 1;
        $b = 5;
        [$a, $b] = [$b, $a];
    }
}
