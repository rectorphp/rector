<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class PhpStan0126Break
{
    public function run(int $item)
    {
        if ($item) {
            return 'not empty';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class PhpStan0126Break
{
    public function run(int $item)
    {
        if ($item !== 0) {
            return 'not empty';
        }
    }
}

?>
