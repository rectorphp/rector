<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class Nullable
{
    public function run(?\stdClass $item)
    {
        if (!$item) {
            return 'empty';
        }
    }

    public function go(?\stdClass $item)
    {
        if ($item) {
            return 'not empty';
        }
    }

    public function away(?string $item)
    {
        if (!$item) {
            return 'empty';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class Nullable
{
    public function run(?\stdClass $item)
    {
        if ($item === null) {
            return 'empty';
        }
    }

    public function go(?\stdClass $item)
    {
        if ($item !== null) {
            return 'not empty';
        }
    }

    public function away(?string $item)
    {
        if (!$item) {
            return 'empty';
        }
    }
}

?>
