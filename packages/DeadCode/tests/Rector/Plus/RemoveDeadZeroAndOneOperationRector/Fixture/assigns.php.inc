<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class Assigns
{
    public function run(int $value)
    {
        $value -= 0;
        $value += 0;
        $value *= 1;
        $value /= 1;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class Assigns
{
    public function run(int $value)
    {
        $value;
        $value;
        $value;
        $value;
    }
}

?>
