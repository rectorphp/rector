<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class NoUnpacks
{
    public function run()
    {
        $fruits = ['apple', 'pear'];
        $colors = array('blue', 'yellow');
    }
}

?>
