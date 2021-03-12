<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class SkipHasElseif
{
    public function go(stdClass $var)
    {
        if (! $var instanceof stdClass) {
            return false;
        } elseif ($var->foo === 'foo') {
            echo 'statement';
            return true;
        }
    }
}

?>
