<?php

namespace Rector\Tests\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector\Fixture;

class SkipNotFreadFwrite
{
    public function run()
    {
        strpos('test', 't') === false;
    }
}

?>
