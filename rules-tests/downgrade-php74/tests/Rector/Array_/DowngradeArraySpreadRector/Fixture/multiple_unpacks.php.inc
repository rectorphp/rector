<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class MultipleUnpacks
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

class MultipleUnpacks
{
    public function run()
    {
        $parts1 = ['apple', 'pear'];
        $parts2 = ['orange'];
        $parts3 = ['banana', 'kiwi', 'pomelo'];
        $fruits = array_merge($parts1, $parts2, $parts3);
    }
}

?>
