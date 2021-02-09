<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class Fixture
{
    public function go(stdClass $stdClass)
    {
        if (! $stdClass instanceof stdClass) {
            return false;
        }
        return true;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class Fixture
{
    public function go(stdClass $stdClass)
    {
        return true;
    }
}

?>
