<?php

namespace Rector\Tests\Rector\Exclusion\Check\OtherDocBlocks;


final class SomeController
{
    public function foo()
    {
        /**
         * @noRector \Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector
         * @noRector \Rector\DeadCode\Rector\Plus\AnotherRector
         */
        round(1 + 0);

        /** @noRector \Rector\DeadCode\Rector\Plus\AnotherRector */
        round(2 + 0);
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Exclusion\Check\OtherDocBlocks;


final class SomeController
{
    public function foo()
    {
        /**
         * @noRector \Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector
         * @noRector \Rector\DeadCode\Rector\Plus\AnotherRector
         */
        round(1 + 0);

        /** @noRector \Rector\DeadCode\Rector\Plus\AnotherRector */
        round(2);
    }
}

?>
