<?php

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class SkipPrevNotReturn
{
    public function run()
    {
        if (1 === 1) {
            echo 'statement';
        }

        return 'yes';
    }
}

?>
