<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\EmptyIf;

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
