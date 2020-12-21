<?php

namespace Rector\DowngradePhp74\Tests\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\Fixture;

class Fixture
{
    public function run()
    {
        $int = 1_000;
        $float = 1_000_500.001;
        $negative_int = -1_000;
        $negative_float = -1_000_500.001;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\Fixture;

class Fixture
{
    public function run()
    {
        $int = 1000;
        $float = 1000500.001;
        $negative_int = -1000;
        $negative_float = -1000500.001;
    }
}

?>
