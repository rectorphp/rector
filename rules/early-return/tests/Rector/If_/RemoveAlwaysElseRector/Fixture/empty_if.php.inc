<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElse\EmptyIf;

class SomeClass
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
