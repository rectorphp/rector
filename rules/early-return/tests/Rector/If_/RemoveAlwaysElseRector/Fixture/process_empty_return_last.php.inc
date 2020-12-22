<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class ProcessEmptyReturnLast
{
    public function firstRun($value)
    {
        if ($value) {
            return 5;
        } elseif ($value - 1) {
            $value = 55;
            return 10;
        } else {
            return;
        }
    }

    public function secondRun($value)
    {
        if ($value) {
            return 5;
        }
        if ($value - 1) {
            $value = 55;
            return 10;
        } else {
            return;
        }
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class ProcessEmptyReturnLast
{
    public function firstRun($value)
    {
        if ($value) {
            return 5;
        }
        if ($value - 1) {
            $value = 55;
            return 10;
        } else {
            return;
        }
    }

    public function secondRun($value)
    {
        if ($value) {
            return 5;
        }
        if ($value - 1) {
            $value = 55;
            return 10;
        }
        return;
    }
}

?>
