<?php

namespace Rector\CodeQuality\Tests\Rector\If_\CombineIfRector\Fixture;

class ParentElseif
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
