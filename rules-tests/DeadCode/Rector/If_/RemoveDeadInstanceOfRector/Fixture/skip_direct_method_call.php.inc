<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class SkipDirectMethodCall
{
    private function get(): stdClass
    {
        session_start();

        return new stdClass;
    }

    public function go()
    {
        if (! $this->get() instanceof stdClass) {
            return false;
        }

        return true;
    }
}

?>
