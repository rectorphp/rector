<?php

namespace Rector\Tests\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\Fixture;

class SkipFloatMax
{
    public function run()
    {
        $php_float_max = 1.7976931348623157E+308;
    }
}

?>
