<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\Fixture;

class PushMultiple
{
    public function run()
    {
        $items = [];
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
        $items[] = $item;
        $items[] = $item2;
        $items[] = $item3;
    }
}

?>
