<?php

namespace Rector\Core\Tests\Exclusion\Fixture;

final class DifferentNorector
{
    public function foo()
    {
        /**
         * @noRector \Rector\DeadCode\Rector\Plus\AnotherRector
         */
        round(1 + 0);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Exclusion\Fixture;

final class DifferentNorector
{
    public function foo()
    {
        /**
         * @noRector \Rector\DeadCode\Rector\Plus\AnotherRector
         */
        round(1);
    }
}

?>
