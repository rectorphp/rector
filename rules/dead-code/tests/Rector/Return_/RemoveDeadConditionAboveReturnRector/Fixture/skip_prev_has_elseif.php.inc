<?php

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class SkipPrevHasElseif
{
    public function run()
    {
        if (1 === 1) {
            return 'yes';
        } elseif ( 1 < 2) {
            return 'no';
        }

        return 'yes';
    }
}

?>
