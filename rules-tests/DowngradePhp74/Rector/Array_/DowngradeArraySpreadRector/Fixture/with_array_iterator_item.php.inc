<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class WithArrayIteratorItemClass
{
    public function run()
    {
        $arr7 = [...new ArrayIterator(['a', 'b', 'c'])];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class WithArrayIteratorItemClass
{
    public function run()
    {
        $item0Unpacked = new ArrayIterator(['a', 'b', 'c']);
        $arr7 = array_merge(iterator_to_array($item0Unpacked));
    }
}

?>
