<?php

namespace Rector\CodeQuality\Tests\Rector\Assign\SplitListAssignToSeparateLineRector\Fixture;

final class SkipSwap
{
    public function run(): void
    {
        $a = 1;
        $b = 5;
        [$a, $b] = [$b, $a];
    }
}
