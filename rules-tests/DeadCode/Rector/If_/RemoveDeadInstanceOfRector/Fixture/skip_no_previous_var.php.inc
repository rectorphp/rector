<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class SkipNoPreviousVar
{
    public function go()
    {
        if (! $this->var instanceof stdClass) {
            return false;
        }
        return true;
    }
}

?>
