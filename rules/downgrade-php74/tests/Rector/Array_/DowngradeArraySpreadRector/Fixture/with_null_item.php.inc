<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithNullItem
{
    public function run()
    {
        $parts = ['apple', 'pear', null];
        $fruits = ['banana', 'orange', ...$parts, null, 'watermelon'];
        $fruits = [...$parts, null];
        $fruits = [null, ...$parts];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithNullItem
{
    public function run()
    {
        $parts = ['apple', 'pear', null];
        $fruits = array_merge(['banana', 'orange'], $parts, [null, 'watermelon']);
        $fruits = array_merge($parts, [null]);
        $fruits = array_merge([null], $parts);
    }
}

?>
