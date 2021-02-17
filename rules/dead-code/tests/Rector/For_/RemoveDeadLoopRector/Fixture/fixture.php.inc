<?php

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadLoopRector\Fixture;

class Fixture
{
    public function run($values)
    {
        for ($i=1; $i<count($values); ++$i) {
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadLoopRector\Fixture;

class Fixture
{
    public function run($values)
    {
    }
}

?>
