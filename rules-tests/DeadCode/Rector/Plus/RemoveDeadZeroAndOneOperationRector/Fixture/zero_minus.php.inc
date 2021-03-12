<?php

namespace Rector\Tests\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class ZeroMinus
{
    public function run(int $value)
    {
        $value = 0 - $value;
    }
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class ZeroMinus
{
    public function run(int $value)
    {
        $value = -$value;
    }
}
?>
