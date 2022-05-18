<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

class NopStmt
{
    public function run()
    {
        if (true === true) {
            // some comment
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

class NopStmt
{
    public function run()
    {
        // some comment

    }
}

?>
