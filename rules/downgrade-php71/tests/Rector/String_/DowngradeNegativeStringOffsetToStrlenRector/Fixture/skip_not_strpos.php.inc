<?php

namespace Rector\DowngradePhp71\Tests\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

class SkipNotStrpos
{
    public function run($var)
    {
        echo substr($var, -1);
    }
}

?>
