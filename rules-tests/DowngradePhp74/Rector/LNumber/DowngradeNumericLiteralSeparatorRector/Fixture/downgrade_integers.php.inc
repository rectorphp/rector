<?php

namespace Rector\Tests\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\Fixture;

final class DowngradeIntegers
{
    public function run()
    {
        $int = 1_000;
        $negative_int = -1_000;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\Fixture;

final class DowngradeIntegers
{
    public function run()
    {
        $int = 1000;
        $negative_int = -1000;
    }
}

?>
