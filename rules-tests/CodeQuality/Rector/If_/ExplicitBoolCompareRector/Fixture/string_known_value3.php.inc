<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class StringSingleChar
{
    public function run()
    {
        $item = 'a';

        if (!$item) {
            return 'empty';
        }
    }

    public function run2()
    {
        $item = 'a';

        if ($item) {
            return 'not empty';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class StringSingleChar
{
    public function run()
    {
        $item = 'a';

        if ($item === '0') {
            return 'empty';
        }
    }

    public function run2()
    {
        $item = 'a';

        if ($item !== '0') {
            return 'not empty';
        }
    }
}

?>
