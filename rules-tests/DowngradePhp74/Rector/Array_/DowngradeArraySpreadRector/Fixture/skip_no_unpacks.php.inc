<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class SkipNoUnpacks
{
    public function run()
    {
        $fruits = ['apple', 'pear'];
        $colors = array('blue', 'yellow');
    }
}

?>
