<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

class ProcessEmptyReturnLast
{
    public function runAgain($value)
    {
        if ($value) {
            return 5;
        } elseif ($value- 1) {
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

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

class ProcessEmptyReturnLast
{
    public function runAgain($value)
    {
        if ($value) {
            return 5;
        } elseif ($value- 1) {
            $value = 55;
            return 10;
        }
        return;
    }
}

?>
