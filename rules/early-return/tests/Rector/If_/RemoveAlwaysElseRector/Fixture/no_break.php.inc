<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElse\NoBreak;

class SomeClass
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
