<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class WithMultipleArrayIteratorItemsClass
{
    public function run()
    {
        $arr7 = [...new ArrayIterator(['a', 'b', 'c']), ...new ArrayIterator(['e', 'f'])];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class WithMultipleArrayIteratorItemsClass
{
    public function run()
    {
        $item0Unpacked = new ArrayIterator(['a', 'b', 'c']);
        $item1Unpacked = new ArrayIterator(['e', 'f']);
        $arr7 = array_merge(iterator_to_array($item0Unpacked), iterator_to_array($item1Unpacked));
    }
}

?>
