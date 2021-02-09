<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class Truthy
{
    public function go(stdClass $var)
    {
        if ($var instanceof stdClass) {
            echo 'statement';
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

class Truthy
{
    public function go(stdClass $var)
    {
        echo 'statement';
        return false;
        return true;
    }
}

?>
