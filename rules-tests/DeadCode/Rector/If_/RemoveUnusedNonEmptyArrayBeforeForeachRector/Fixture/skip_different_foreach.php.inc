<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

class SkipDifferentForeach
{
    public function run()
    {
        $values = [];
        $values2 = [];

        if ($values != []) {
            foreach ($values2 as $value) {
                echo $value;
            }
        }
    }
}
