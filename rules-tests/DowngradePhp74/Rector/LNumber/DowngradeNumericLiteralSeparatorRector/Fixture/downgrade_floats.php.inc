<?php

namespace Rector\Tests\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\Fixture;

final class DowngradeFloats
{
    public function run()
    {
        $float = 1_000_500.001;
        $negative_float = -1_000_500.001;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\Fixture;

final class DowngradeFloats
{
    public function run()
    {
        $float = 1000500.001;
        $negative_float = -1000500.001;
    }
}

?>
