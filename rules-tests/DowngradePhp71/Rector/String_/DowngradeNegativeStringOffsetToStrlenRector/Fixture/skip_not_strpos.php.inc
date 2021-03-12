<?php

namespace Rector\Tests\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

class SkipNotStrpos
{
    public function run($var)
    {
        echo substr($var, -1);
    }
}

?>
