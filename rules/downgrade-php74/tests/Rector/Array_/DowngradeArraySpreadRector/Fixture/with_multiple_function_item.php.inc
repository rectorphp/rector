<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithMultipleFunctionItem
{
    function getFirstArray(): array
    {
        return ['apple', 'pear'];
    }

    function getSecondArray(): array
    {
        return ['durian', 'kiwi'];
    }

    public function run()
    {
        $fruits = ['banana', 'orange', ...$this->getFirstArray(), 'watermelon', ...$this->getSecondArray()];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithMultipleFunctionItem
{
    function getFirstArray(): array
    {
        return ['apple', 'pear'];
    }

    function getSecondArray(): array
    {
        return ['durian', 'kiwi'];
    }

    public function run()
    {
        $item2Unpacked = $this->getFirstArray();
        $item4Unpacked = $this->getSecondArray();
        $fruits = array_merge(['banana', 'orange'], $item2Unpacked, ['watermelon'], $item4Unpacked);
    }
}

?>
