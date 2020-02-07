<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\Fixture;

class SomeClass
{
    public function run()
    {
        $items = [];
        array_push($items, $item);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\Fixture;

class SomeClass
{
    public function run()
    {
        $items = [];
        $items[] = $item;
    }
}

?>
