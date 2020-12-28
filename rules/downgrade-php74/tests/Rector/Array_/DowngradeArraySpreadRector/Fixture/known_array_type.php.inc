<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class KnownArrayType
{
    public function run(array $parts)
    {
        $fruits = array('banana', 'orange', ...$parts, 'watermelon');
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class KnownArrayType
{
    public function run(array $parts)
    {
        $fruits = array_merge(['banana', 'orange'], $parts, ['watermelon']);
    }
}

?>
