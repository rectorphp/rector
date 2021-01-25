<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class SkipEmptyIf
{
    public function run()
    {
        if ($cond1) {

        } else {
            foo();
            return 'bar';
        }
    }
}

?>
