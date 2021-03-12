<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadIfForeachForRector\Fixture;

class SideEffectChecks
{
    public function run()
    {
        if (10) {
        }

        if (true) {
        }

        if (5 + 10) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadIfForeachForRector\Fixture;

class SideEffectChecks
{
    public function run()
    {
        if (5 + 10) {
        }
    }
}

?>
