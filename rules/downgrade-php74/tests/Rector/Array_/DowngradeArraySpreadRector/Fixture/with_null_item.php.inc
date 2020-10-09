<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithNullItemClass
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

class WithNullItemClass
{
    public function run()
    {
        $parts = ['apple', 'pear', null];
        $fruits = array_merge(['banana', 'orange'], is_array($parts) ? $parts : iterator_to_array($parts), [null, 'watermelon']);
        $fruits = array_merge(is_array($parts) ? $parts : iterator_to_array($parts), [null]);
        $fruits = array_merge([null], is_array($parts) ? $parts : iterator_to_array($parts));
    }
}

?>
