<?php

namespace Rector\DowngradePhp74\Tests\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector\Fixture;

class SkipNotCompareWithFalse
{
    public function run($value)
    {
        fread($handle, $length) === $value;
        fwrite($fp, '1') === $value;
    }
}

?>
