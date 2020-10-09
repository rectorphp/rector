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
        $arr7 = array_merge(is_array(new ArrayIterator(['a', 'b', 'c'])) ? new ArrayIterator(['a', 'b', 'c']) : iterator_to_array(new ArrayIterator(['a', 'b', 'c'])));
    }
}

?>
