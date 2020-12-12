<?php

namespace Rector\Core\Tests\Exclusion\Check\Fixture;


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
