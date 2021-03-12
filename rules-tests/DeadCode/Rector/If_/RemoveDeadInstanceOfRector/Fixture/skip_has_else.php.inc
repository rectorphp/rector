<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class SkipHasElse
{
    public function go(stdClass $var)
    {
        if (! $var instanceof stdClass) {
            return false;
        } else {
            echo 'statement';
            return true;
        }
    }
}

?>
