<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class SingleUnpack
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = array_merge($parts);
    }
}

?>
