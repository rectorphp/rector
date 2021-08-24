<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

class SkipOnNativeVariable
{
    public function run()
    {
        if (!empty($_SESSION)) {
            foreach ($_SESSION as $value) {
                echo $value;
            }
        }
    }
}

?>