<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class Array_
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

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class Array_
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
