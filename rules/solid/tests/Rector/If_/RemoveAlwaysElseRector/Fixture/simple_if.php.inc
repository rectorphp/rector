<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class SimpleIf
{
    public function run()
    {
        if ($cond1) {
            return 'foo';
        }
    }
}

?>
