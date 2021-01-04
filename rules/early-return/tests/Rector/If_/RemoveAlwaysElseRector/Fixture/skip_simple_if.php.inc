<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

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
