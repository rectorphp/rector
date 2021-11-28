<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadContinueRector\Fixture;

class Foreach_
{
    public function run($values)
    {
        foreach ($values as $value) {
            var_dump($value);
            continue;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadContinueRector\Fixture;

class Foreach_
{
    public function run($values)
    {
        foreach ($values as $value) {
            var_dump($value);
        }
    }
}

?>
