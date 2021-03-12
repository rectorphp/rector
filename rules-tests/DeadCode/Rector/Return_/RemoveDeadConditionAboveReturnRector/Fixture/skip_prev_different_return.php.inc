<?php

namespace Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

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
