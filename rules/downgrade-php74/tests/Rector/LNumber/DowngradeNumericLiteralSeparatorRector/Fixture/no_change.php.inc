<?php

namespace Rector\DowngradePhp74\Tests\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\Fixture;

class NoChange
{
    public function run()
    {
        $int = 1000;
        $float = 1000500.001;
        $forced_float = 1000500.0;
        $negative_int = -1000;
        $negative_float = -1000500.001;
        $binary = 0b11111111;
        $octal = 0123;
        $hex = 0x1A;
    }
}

?>
