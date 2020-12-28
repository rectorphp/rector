<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class Phpstan0126break
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

final class Phpstan0126break
{
    public function run(int $item)
    {
        if ($item !== 0) {
            return 'not empty';
        }
    }
}

?>
