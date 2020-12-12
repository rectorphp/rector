<?php

namespace Rector\DowngradePhp71\Tests\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector;

class SkipNoOffset
{
    public function run($var)
    {
        echo strpos('aabbcc', 'b');
    }
}

?>
