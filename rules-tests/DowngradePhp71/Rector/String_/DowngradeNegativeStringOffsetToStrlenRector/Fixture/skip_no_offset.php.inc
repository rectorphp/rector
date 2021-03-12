<?php

namespace Rector\Tests\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

class SkipNoOffset
{
    public function run($var)
    {
        echo strpos('aabbcc', 'b');
    }
}

?>
