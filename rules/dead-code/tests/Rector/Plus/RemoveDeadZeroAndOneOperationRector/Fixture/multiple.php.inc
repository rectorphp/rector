<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class Multiple
{
    public function run()
    {
        $value = 0 + 5 + 0;
        $value = 5 + 0 + 5 + 0;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\Fixture;

class Multiple
{
    public function run()
    {
        $value = 5;
        $value = 5 + 5;
    }
}

?>
