<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\RemoveAlwaysElseRector\Fixture;

class SkipNoBreak
{
    public function run()
    {
        if ($cond1) {
            foo();
        } else {
            return 'bar';
        }
    }
}

?>
