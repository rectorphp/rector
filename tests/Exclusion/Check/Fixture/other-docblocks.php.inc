<?php

namespace Rector\Core\Tests\Rector\Exclusion\Check\OtherDocBlocks;


final class SomeController
{
    public function foo()
    {
        /**
         * @noRector \Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector
         * @noRector \Rector\DeadCode\Rector\Plus\AnotherRector
         */
        round(1 + 0);

        /** @noRector \Rector\DeadCode\Rector\Plus\AnotherRector */
        round(2 + 0);

        /** @noRector */
        round(2 + 0);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Exclusion\Check\OtherDocBlocks;


final class SomeController
{
    public function foo()
    {
        /**
         * @noRector \Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector
         * @noRector \Rector\DeadCode\Rector\Plus\AnotherRector
         */
        round(1 + 0);

        /** @noRector \Rector\DeadCode\Rector\Plus\AnotherRector */
        round(2);

        /** @noRector */
        round(2 + 0);
    }
}

?>
