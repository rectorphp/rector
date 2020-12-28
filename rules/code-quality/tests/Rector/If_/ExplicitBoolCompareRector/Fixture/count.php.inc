<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class Count
{
    public function run($items)
    {
        if (!count($items)) {
            return 'no items';
        } elseif (count($items)) {
            return 'many items';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class Count
{
    public function run($items)
    {
        if (count($items) === 0) {
            return 'no items';
        } elseif (count($items) > 0) {
            return 'many items';
        }
    }
}

?>
