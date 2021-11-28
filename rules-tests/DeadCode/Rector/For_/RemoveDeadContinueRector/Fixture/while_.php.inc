<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadContinueRector\Fixture;

class While_
{
    public function run()
    {
       while (++$i < 10) {
           echo $i;
           continue;
       }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadContinueRector\Fixture;

class While_
{
    public function run()
    {
       while (++$i < 10) {
           echo $i;
       }
    }
}

?>
