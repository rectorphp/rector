<?php

namespace Rector\Tests\Rector\Exclusion\Check\Fixture1NoRector;


final class SomeController
{
    public function foo()
    {
        /** @noRector \Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector */
        round(1 + 0);

        /** @noRector Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector */
        round(2 + 0);

        /** @noRector \Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector */
        round(round(3 + 0));

        round(/** @noRector \Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector */ 4 + 0);
    }
}

?>
