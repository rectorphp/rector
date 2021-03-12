<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithUnknownTypeFunctionItem
{
    function getArrayOrTraversable()
    {
        return ['apple', 'pear'];
    }

    public function run()
    {
        $fruits = ['banana', 'orange', ...$this->getArrayOrTraversable(), 'watermelon'];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithUnknownTypeFunctionItem
{
    function getArrayOrTraversable()
    {
        return ['apple', 'pear'];
    }

    public function run()
    {
        $item2Unpacked = $this->getArrayOrTraversable();
        $fruits = array_merge(['banana', 'orange'], is_array($item2Unpacked) ? $item2Unpacked : iterator_to_array($item2Unpacked), ['watermelon']);
    }
}

?>
