<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\Fixture;

class PushMultiple
{
    public function run()
    {
        $items = [];
        // keep comment
        array_push($items, $item, $item2, $item3);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\Fixture;

class PushMultiple
{
    public function run()
    {
        $items = [];
        // keep comment
        $items[] = $item;
        $items[] = $item2;
        $items[] = $item3;
    }
}

?>
