<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class DifferentPositions
{
    public function run()
    {
        $parts1 = ['apple', 'pear'];
        $parts2 = ['orange'];
        $parts3 = ['banana', 'kiwi', 'pomelo'];
        $fruits = [...$parts1, ...$parts2, ...$parts3];
        $fruits = ['durian', ...$parts1, ...$parts2, ...$parts3];
        $fruits = [...$parts1, ...$parts2, ...$parts3, 'durian'];
        $fruits = ['rambutan', 'longan', ...$parts1, ...$parts2, ...$parts3, 'durian'];
        $fruits = ['rambutan', 'longan', ...$parts1, ...$parts2, ...$parts3, 'durian'];
        $fruits = ['rambutan', 'longan', ...$parts1, ...$parts2, 'watermelon', ...$parts3, 'durian'];
        $fruits = ['rambutan', 'longan', ...$parts1, ...$parts2, 'watermelon', ...$parts3];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class DifferentPositions
{
    public function run()
    {
        $parts1 = ['apple', 'pear'];
        $parts2 = ['orange'];
        $parts3 = ['banana', 'kiwi', 'pomelo'];
        $fruits = array_merge($parts1, $parts2, $parts3);
        $fruits = array_merge(['durian'], $parts1, $parts2, $parts3);
        $fruits = array_merge($parts1, $parts2, $parts3, ['durian']);
        $fruits = array_merge(['rambutan', 'longan'], $parts1, $parts2, $parts3, ['durian']);
        $fruits = array_merge(['rambutan', 'longan'], $parts1, $parts2, $parts3, ['durian']);
        $fruits = array_merge(['rambutan', 'longan'], $parts1, $parts2, ['watermelon'], $parts3, ['durian']);
        $fruits = array_merge(['rambutan', 'longan'], $parts1, $parts2, ['watermelon'], $parts3);
    }
}

?>
