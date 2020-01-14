<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class Numbers
{
    public function run(int $item)
    {
        if (!$item) {
            return 'empty';
        }
    }

    public function go(float $item)
    {
        if (!$item) {
            return 'empty';
        }

        if ($item) {
            return 'not empty';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class Numbers
{
    public function run(int $item)
    {
        if ($item === 0) {
            return 'empty';
        }
    }

    public function go(float $item)
    {
        if ($item === 0.0) {
            return 'empty';
        }

        if ($item !== 0.0) {
            return 'not empty';
        }
    }
}

?>
