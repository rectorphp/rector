<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveLastReturnRector\Fixture;

class InInnerClosure
{
    function run($value) {
        $value = (bool) function ($value) {
            if ($value === 1000) {
                return;
            }

            if ($value) {
                return;
            }
        };

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveLastReturnRector\Fixture;

class InInnerClosure
{
    function run($value) {
        $value = (bool) function ($value) {
            if ($value === 1000) {
                return;
            }

            if ($value) {
            }
        };

        return $value;
    }
}

?>
