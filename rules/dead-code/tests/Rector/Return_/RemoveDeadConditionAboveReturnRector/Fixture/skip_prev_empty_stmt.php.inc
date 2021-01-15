<?php

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class SkipPrevEmptyStmt
{
    public function run()
    {
        if (1 === 1) {

        }

        return 'yes';
    }
}

?>
