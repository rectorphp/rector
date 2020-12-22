<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToCoalescingRector\Fixture;

final class SkipNonValueReturn
{
    public function run(array $oldToNewFunctions, $currentFunction)
    {
        foreach ($oldToNewFunctions as $oldFunction => $newFunction) {
            if ($currentFunction === $oldFunction) {
                return 15;
            }
        }

        return 0;
    }
}
