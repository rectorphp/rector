<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class WithTernary
{
    public function run(int $item)
    {
        return $item ? 1 : 0;

        return $item ?: 0;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class WithTernary
{
    public function run(int $item)
    {
        return $item !== 0 ? 1 : 0;

        return $item ?: 0;
    }
}

?>
