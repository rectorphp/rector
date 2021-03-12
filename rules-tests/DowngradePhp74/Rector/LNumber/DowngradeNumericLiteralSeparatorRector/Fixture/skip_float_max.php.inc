<?php

namespace Rector\DowngradePhp74\Tests\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\Fixture;

class SkipFloatMax
{
    public function run()
    {
        $php_float_max = 1.7976931348623157E+308;
    }
}

?>
