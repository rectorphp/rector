<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\NoBreak;

class SomeClass {
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
