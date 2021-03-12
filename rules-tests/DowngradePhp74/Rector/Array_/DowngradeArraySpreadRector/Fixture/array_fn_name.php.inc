<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class ArrayFnName
{
    public function run()
    {
        $parts = array('apple', 'pear');
        $fruits = array('banana', 'orange', ...$parts, 'watermelon');
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class ArrayFnName
{
    public function run()
    {
        $parts = array('apple', 'pear');
        $fruits = array_merge(['banana', 'orange'], $parts, ['watermelon']);
    }
}

?>
