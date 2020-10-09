<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithArrayItemClass
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

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithArrayItemClass
{
    public function run()
    {
        $parts = [['apple', 'pear']];
        $fruits = array_merge([['banana', 'orange']], is_array($parts) ? $parts : iterator_to_array($parts), [['watermelon']]);
    }
}

?>
