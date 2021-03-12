<?php

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class SkipPrevDifferentReturn
{
    public function run()
    {
        if (1 === 1) {
            echo 'no';
        }

        return 'yes';
    }
}

?>
