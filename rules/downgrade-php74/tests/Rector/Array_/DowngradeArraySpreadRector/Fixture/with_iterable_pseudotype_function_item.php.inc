<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class WithIterablePseudotypeFunctionItemClass
{
    function getIterator(): iterable
    {
        return new ArrayIterator(['apple', 'pear']);
    }

    public function run()
    {
        $fruits = ['banana', 'orange', ...$this->getIterator(), 'watermelon'];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use ArrayIterator;

class WithIterablePseudotypeFunctionItemClass
{
    function getIterator(): iterable
    {
        return new ArrayIterator(['apple', 'pear']);
    }

    public function run()
    {
        $item2Unpacked = $this->getIterator();
        $fruits = array_merge(['banana', 'orange'], iterator_to_array($item2Unpacked), ['watermelon']);
    }
}

?>
