<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithFunctionItemClass
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run()
    {
        $fruits = ['banana', 'orange', ...$this->getArray(), 'watermelon'];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithFunctionItemClass
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run()
    {
        $item2Unpacked = $this->getArray();
        $fruits = array_merge(['banana', 'orange'], is_array($item2Unpacked) ? $item2Unpacked : iterator_to_array($item2Unpacked), ['watermelon']);
    }
}

?>
