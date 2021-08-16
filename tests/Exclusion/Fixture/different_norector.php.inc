<?php

namespace Rector\Core\Tests\Exclusion\Fixture;

final class DifferentNorector
{
    public function foo()
    {
        /**
         * @noRector \Rector\Core\Tests\Exclusion\Source\SomeRector
         */
        round(1 + 0);

        // @noRector \Rector\Core\Tests\Exclusion\Source\SomeRector
        round(2 + 0);
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
         * @noRector \Rector\Core\Tests\Exclusion\Source\SomeRector
         */
        round(1);

        // @noRector \Rector\Core\Tests\Exclusion\Source\SomeRector
        round(2);
    }
}

?>
