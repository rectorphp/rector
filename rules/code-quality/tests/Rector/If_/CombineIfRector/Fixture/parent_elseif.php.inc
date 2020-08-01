<?php

namespace Rector\CodeQuality\Tests\Rector\If_\CombineIfRector\ParentElseIf;

class SomeClass
{
    public function run()
    {
        if ($cond1) {
            if ($cond2) {
                return 'foo';
            }
        } elseif($cond3) {
            return 'bar';
        }
    }
}
