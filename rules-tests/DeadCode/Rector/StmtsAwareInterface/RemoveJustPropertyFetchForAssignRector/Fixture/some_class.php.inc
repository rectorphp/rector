<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector\Fixture;

class SomeClass
{
    private $items = [];

    public function run()
    {
        $items = $this->items;
        $items[] = 1000;
        $this->items = $items ;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector\Fixture;

class SomeClass
{
    private $items = [];

    public function run()
    {
        $this->items[] = 1000;
    }
}

?>
