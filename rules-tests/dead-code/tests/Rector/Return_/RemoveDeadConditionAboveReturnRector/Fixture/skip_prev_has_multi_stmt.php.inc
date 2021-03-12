<?php

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class SkipPrevHasMultiStmt
{
    public function run()
    {
        if (1 === 1) {
            echo 'statement';
            return 'yes';
        }

        return 'yes';
    }
}

?>
