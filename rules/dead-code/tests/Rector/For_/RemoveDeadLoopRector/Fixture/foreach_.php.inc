<?php

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadLoopRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadLoopRector\Fixture;

class Foreach_
{
    public function run($values)
    {
    }
}

?>
