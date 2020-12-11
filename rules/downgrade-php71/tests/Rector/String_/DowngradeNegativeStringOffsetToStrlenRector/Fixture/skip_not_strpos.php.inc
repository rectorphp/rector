<?php

namespace Rector\DowngradePhp71\Tests\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector;

class SkipNotStrpos
{
    public function run($var)
    {
        echo substr($var, -1);
    }
}

?>
