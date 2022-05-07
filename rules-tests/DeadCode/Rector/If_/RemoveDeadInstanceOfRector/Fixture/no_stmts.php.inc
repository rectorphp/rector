<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class NoStmts
{
    public function go(stdClass $var)
    {
        if ($var instanceof stdClass) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class NoStmts
{
    public function go(stdClass $var)
    {
    }
}

?>
