<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class Fixture
{
    public function run($items)
    {
        $items2 = [];
        foreach ($items as $item) {
             $items2[] = $item;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class Fixture
{
    public function run($items)
    {
        $items2 = [];
        $items2 = $items;
    }
}

?>
