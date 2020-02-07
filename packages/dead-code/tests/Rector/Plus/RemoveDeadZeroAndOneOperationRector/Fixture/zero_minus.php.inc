<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\ZeroMinus;

class Assigns
{
    public function run(int $value)
    {
        $value = 0 - $value;
    }
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\ZeroMinus;

class Assigns
{
    public function run(int $value)
    {
        $value = -$value;
    }
}
?>
