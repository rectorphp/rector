<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use Traversable;

class KnownIteratorTypeClass
{
    public function run(Traversable $parts)
    {
        $fruits = array('banana', 'orange', ...$parts, 'watermelon');
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\Fixture;

use Traversable;

class KnownIteratorTypeClass
{
    public function run(Traversable $parts)
    {
        $fruits = array_merge(['banana', 'orange'], iterator_to_array($parts), ['watermelon']);
    }
}

?>
