<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class SkipNoUnpacks
{
    public function run()
    {
        $fruits = ['apple', 'pear'];
        $colors = array('blue', 'yellow');
    }
}

?>
