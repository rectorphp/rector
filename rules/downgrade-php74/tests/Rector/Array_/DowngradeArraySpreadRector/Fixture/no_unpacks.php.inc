<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class NoUnpacksClass
{
    public function run()
    {
        $fruits = ['apple', 'pear'];
        $colors = array('blue', 'yellow');
    }
}

?>
