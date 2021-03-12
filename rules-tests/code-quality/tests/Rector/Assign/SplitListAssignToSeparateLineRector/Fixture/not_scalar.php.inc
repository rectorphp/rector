<?php

namespace Rector\CodeQuality\Tests\Rector\Assign\SplitListAssignToSeparateLineRector\Fixture;

final class NotScalar
{
    public function run(): void
    {
        $itemCount = 100;
        [$a, $b] = [1, $itemCount];
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Assign\SplitListAssignToSeparateLineRector\Fixture;

final class NotScalar
{
    public function run(): void
    {
        $itemCount = 100;
        $a = 1;
        $b = $itemCount;
    }
}

?>
