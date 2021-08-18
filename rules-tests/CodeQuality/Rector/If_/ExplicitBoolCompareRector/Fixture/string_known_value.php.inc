<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class StringKnownvalue
{
    public function run()
    {
        $item = 'a value';

        if (!$item) {
            return 'empty';
        }
    }

    public function run2()
    {
        $item = 'a value';

        if ($item) {
            return 'not empty';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class StringKnownvalue
{
    public function run()
    {
        $item = 'a value';

        if ($item === '') {
            return 'empty';
        }
    }

    public function run2()
    {
        $item = 'a value';

        if ($item !== '') {
            return 'not empty';
        }
    }
}

?>
