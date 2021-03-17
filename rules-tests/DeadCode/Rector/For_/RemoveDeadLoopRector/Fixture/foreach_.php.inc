<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadLoopRector\Fixture;

class Foreach_
{
    public function run($values)
    {
        foreach ($values as $value) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadLoopRector\Fixture;

class Foreach_
{
    public function run($values)
    {
    }
}

?>
