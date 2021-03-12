<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithArrayItem
{
    public function run()
    {
        $parts = [['apple', 'pear']];
        $fruits = [['banana', 'orange'], ...$parts, ['watermelon']];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithArrayItem
{
    public function run()
    {
        $parts = [['apple', 'pear']];
        $fruits = array_merge([['banana', 'orange']], $parts, [['watermelon']]);
    }
}

?>
