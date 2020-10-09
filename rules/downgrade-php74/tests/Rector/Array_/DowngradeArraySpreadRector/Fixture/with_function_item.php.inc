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
        $fruits = array_merge(['banana', 'orange'], is_array($this->getArray()) ? $this->getArray() : iterator_to_array($this->getArray()), ['watermelon']);
    }
}

?>
