<?php

namespace Rector\Tests\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector\Fixture;

class KeepInCondition
{
    public function run()
    {
        $tokens = [];
        if ($token = 4 and $tokens[] = $token) {
            return true;
        }

        return $tokens;
    }
}
