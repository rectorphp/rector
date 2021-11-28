<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadContinueRector\Fixture;

class Fixture
{
    public function run($values)
    {
        for ($i=1; $i<count($values); ++$i) {
            echo $i;
            continue;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadContinueRector\Fixture;

class Fixture
{
    public function run($values)
    {
        for ($i=1; $i<count($values); ++$i) {
            echo $i;
        }
    }
}

?>
