<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\SimpleIf;

class SomeClass
{
    public function run()
    {
        if ($cond1) {
            return 'foo';
        }
    }
}

?>
