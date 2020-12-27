<?php

namespace Rector\Core\Tests\Exclusion\Fixture;

final class SkipOtherDocblocks
{
    public function foo()
    {
        /**
         * @noRector \Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector
         * @noRector \Rector\DeadCode\Rector\Plus\AnotherRector
         */
        round(1 + 0);

        /** @noRector */
        round(2 + 0);
    }
}
