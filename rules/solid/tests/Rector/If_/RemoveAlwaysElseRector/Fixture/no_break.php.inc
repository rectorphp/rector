<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class NoBreak
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
