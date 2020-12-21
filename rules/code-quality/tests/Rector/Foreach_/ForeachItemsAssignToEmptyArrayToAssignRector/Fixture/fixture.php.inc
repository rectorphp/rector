<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

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

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class Fixture
{
    public function run($items)
    {
        $items2 = [];
        $items2 = $items;
    }
}

?>
