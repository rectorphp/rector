<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithFunctionItemAndExistingVar
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run()
    {
        $item2Unpacked = 'foo';
        $item2Unpacked1 = 'bar';
        $fruits = ['banana', 'orange', ...$this->getArray(), 'watermelon'];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithFunctionItemAndExistingVar
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run()
    {
        $item2Unpacked = 'foo';
        $item2Unpacked1 = 'bar';
        $item2Unpacked2 = $this->getArray();
        $fruits = array_merge(['banana', 'orange'], $item2Unpacked2, ['watermelon']);
    }
}

?>
