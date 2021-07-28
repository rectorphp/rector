<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use DateTime;
use stdClass;

class SkipNotName
{
    public function go()
    {
        if (rand(0, 1)) {
            $className = new stdClass;
        } else {
            $className = new DateTime('now');
        }

        $obj = new stdClass;
        if ($obj instanceof $className) {
            echo 'it is an stdClass';
        }
    }
}

?>
