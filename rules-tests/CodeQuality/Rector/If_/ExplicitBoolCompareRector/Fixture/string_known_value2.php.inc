<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class StringKnownvalue2
{
    public function run()
    {
        $item = '';

        if (!$item) {
            return 'empty';
        }
    }

    public function run2()
    {
        $item = '';

        if ($item) {
            return 'not empty';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class StringKnownvalue2
{
    public function run()
    {
        $item = '';

        if ($item === '') {
            return 'empty';
        }
    }

    public function run2()
    {
        $item = '';

        if ($item !== '') {
            return 'not empty';
        }
    }
}

?>
