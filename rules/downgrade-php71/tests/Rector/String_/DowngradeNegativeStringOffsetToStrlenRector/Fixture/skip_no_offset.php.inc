<?php

namespace Rector\DowngradePhp71\Tests\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

class SkipNoOffset
{
    public function run($var)
    {
        echo strpos('aabbcc', 'b');
    }
}

?>
