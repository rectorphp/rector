<?php

namespace Rector\Core\Tests\Rector\Exclusion\Check\DocBlockOnParentNoRector;


final class SomeController
{
    public function foo()
    {
        /** @noRector \Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector */
        round(1 + 0);

        /** @noRector Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector */
        round(2 + 0);

        /** @noRector \Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector */
        round(round(3 + 0));

        round(/** @noRector \Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector */ 4 + 0);
    }
}

?>
