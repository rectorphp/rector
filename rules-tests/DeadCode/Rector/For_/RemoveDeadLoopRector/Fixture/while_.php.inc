<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadLoopRector\Fixture;

class While_
{
    public function run()
    {
       while (++$i < 10) {
       }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadLoopRector\Fixture;

class While_
{
    public function run()
    {
    }
}

?>
