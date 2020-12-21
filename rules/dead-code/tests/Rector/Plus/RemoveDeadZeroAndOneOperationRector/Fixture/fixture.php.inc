<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = 5 * 1;
        $value = 1 * 5;
        $value = 5 / 1;
        $value = 5 + 0;
        $value = 5 - 0;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = 5;
        $value = 5;
        $value = 5;
        $value = 5;
        $value = 5;
    }
}

?>
