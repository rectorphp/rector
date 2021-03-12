<?php

namespace Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class PrevEmptyStmt
{
    public function run()
    {
        if (1 === 1) {

        }

        return 'yes';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class PrevEmptyStmt
{
    public function run()
    {
        return 'yes';
    }
}

?>
