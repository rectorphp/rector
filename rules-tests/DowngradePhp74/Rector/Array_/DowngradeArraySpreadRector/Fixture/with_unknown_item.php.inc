<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class WithUnknownItemClass
{
    public function run()
    {
        $rand = rand() ? new ArrayIterator(['a', 'b', 'c']) : ['a', 'b', 'c'];
        $arr7 = [...$rand];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class WithUnknownItemClass
{
    public function run()
    {
        $rand = rand() ? new ArrayIterator(['a', 'b', 'c']) : ['a', 'b', 'c'];
        $arr7 = array_merge(is_array($rand) ? $rand : iterator_to_array($rand));
    }
}

?>
