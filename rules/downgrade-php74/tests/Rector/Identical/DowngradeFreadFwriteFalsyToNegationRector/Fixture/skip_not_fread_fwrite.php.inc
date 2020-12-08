<?php

namespace Rector\DowngradePhp74\Tests\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector\Fixture;

class SkipNotFreadFwrite
{
    public function run()
    {
        strpos('test', 't') === false;
    }
}

?>
