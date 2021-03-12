<?php

namespace Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class SkipPrevNotIf
{
    public function run()
    {
        while ($a) {

        }

        return 'yes';
    }
}

?>
