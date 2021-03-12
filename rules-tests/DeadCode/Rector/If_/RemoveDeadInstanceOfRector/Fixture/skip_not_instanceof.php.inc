<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class SkipNotInstanceOf
{
    public function go(stdClass $var)
    {
        if ($var->foo === 'foo') {

        }
    }
}

?>
