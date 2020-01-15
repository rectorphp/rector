<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\EmptyIf;

class SomeClass {
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
