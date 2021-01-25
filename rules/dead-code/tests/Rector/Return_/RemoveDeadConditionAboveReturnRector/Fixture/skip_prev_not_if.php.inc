<?php

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

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
