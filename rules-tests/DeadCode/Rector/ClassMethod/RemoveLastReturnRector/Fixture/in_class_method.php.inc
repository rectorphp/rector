<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveLastReturnRector\Fixture;

class InClassMethod
{
    function run($value) {
        if ($value === 1000) {
            return;
        }

        if ($value) {
            return;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveLastReturnRector\Fixture;

class InClassMethod
{
    function run($value) {
        if ($value === 1000) {
            return;
        }

        if ($value) {
        }
    }
}

?>
