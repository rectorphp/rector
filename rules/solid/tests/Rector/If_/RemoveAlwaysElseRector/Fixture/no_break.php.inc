<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

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
