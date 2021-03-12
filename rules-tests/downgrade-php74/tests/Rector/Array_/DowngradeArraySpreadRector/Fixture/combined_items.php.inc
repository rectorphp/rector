<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class CombinedItemsClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = ['banana', 'orange', ...$parts, 'watermelon', ...new ArrayIterator(['durian', 'kiwi'])];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class CombinedItemsClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $item4Unpacked = new ArrayIterator(['durian', 'kiwi']);
        $fruits = array_merge(['banana', 'orange'], $parts, ['watermelon'], iterator_to_array($item4Unpacked));
    }
}

?>
