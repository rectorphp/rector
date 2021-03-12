<?php

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadLoopRector\Fixture;

class Do_
{
    public function run()
    {
        do {

        } while (++$i < 10);
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadLoopRector\Fixture;

class Do_
{
    public function run()
    {
    }
}

?>
