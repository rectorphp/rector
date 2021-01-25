<?php

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class PrevEmptyStmt
{
    public function run()
    {
        return 'yes';
    }
}

?>
