<?php

namespace Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class SkipPrevHasElse
{
    public function run()
    {
        if (1 === 1) {
            return 'yes';
        } else {
            return 'no';
        }

        return 'yes';
    }
}

?>
