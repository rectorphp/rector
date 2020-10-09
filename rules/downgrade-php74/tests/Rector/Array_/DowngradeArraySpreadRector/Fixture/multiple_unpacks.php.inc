<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class MultipleUnpacksClass
{
    public function run()
    {
        $parts1 = ['apple', 'pear'];
        $parts2 = ['orange'];
        $parts3 = ['banana', 'kiwi', 'pomelo'];
        $fruits = [...$parts1, ...$parts2, ...$parts3];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class MultipleUnpacksClass
{
    public function run()
    {
        $parts1 = ['apple', 'pear'];
        $parts2 = ['orange'];
        $parts3 = ['banana', 'kiwi', 'pomelo'];
        $fruits = array_merge(is_array($parts1) ? $parts1 : iterator_to_array($parts1), is_array($parts2) ? $parts2 : iterator_to_array($parts2), is_array($parts3) ? $parts3 : iterator_to_array($parts3));
    }
}

?>
