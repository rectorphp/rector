<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class SkipAssignUnion
{
    public function go()
    {
        $var = $this->getStdClass();
        if (! $var instanceof stdClass) {
            return false;
        }
        return true;
    }

    private function getStdClass():?stdClass
    {
        if (rand(0, 1)) {
            return new stdClass;
        }

        return null;
    }
}

?>
