<?php

namespace Rector\CodeQuality\Tests\Rector\If_\CombineIfRector\ParentElse;

class SomeClass
{
    public function run()
    {
        if ($cond1) {
            if ($cond2) {
                return 'foo';
            }
        } else {
            return 'bar';
        }
    }
}
