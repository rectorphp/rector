<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\RemoveAlwaysElseRector\Fixture;

class SkipSimpleIf
{
    public function run()
    {
        if ($cond1) {
            return 'foo';
        }
    }
}

?>
