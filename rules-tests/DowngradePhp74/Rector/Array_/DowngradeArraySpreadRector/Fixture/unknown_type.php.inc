<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class UnknownType
{
    public function run($parts)
    {
        $fruits = array('banana', 'orange', ...$parts, 'watermelon');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class UnknownType
{
    public function run($parts)
    {
        $fruits = array_merge(['banana', 'orange'], is_array($parts) ? $parts : iterator_to_array($parts), ['watermelon']);
    }
}

?>
