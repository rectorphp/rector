<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class ExplicitString
{
    public function run(string $item)
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

final class ExplicitString
{
    public function run(string $item)
    {
        if ($item === '') {
            return 'empty';
        }

        if ($item !== '') {
            return 'not empty';
        }
    }
}

?>
