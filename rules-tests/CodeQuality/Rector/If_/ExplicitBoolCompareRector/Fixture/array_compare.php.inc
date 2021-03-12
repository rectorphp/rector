<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class ArrayCompare
{
    public function run(array $items)
    {
        if (!$items) {
            return 'no items';
        }

        if ($items) {
            return 'many items';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class ArrayCompare
{
    public function run(array $items)
    {
        if ($items === []) {
            return 'no items';
        }

        if ($items !== []) {
            return 'many items';
        }
    }
}

?>
