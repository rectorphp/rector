<?php

namespace Rector\DowngradePhp71\Tests\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector;

class SkipPositive
{
    public function run($var)
    {
        echo 'abcdef'[2];
        echo strpos('aabbcc', 'b', 3);
        echo strpos($var, 'b', 3);
    }
}

?>
