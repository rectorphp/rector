<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class SingleUnpack
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = [...$parts];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class SingleUnpack
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = array_merge($parts);
    }
}

?>
