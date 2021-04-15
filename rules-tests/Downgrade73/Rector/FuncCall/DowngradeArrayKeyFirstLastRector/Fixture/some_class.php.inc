<?php

namespace Rector\Tests\Downgrade73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\Fixture;

class SomeClass
{
    public function run($items)
    {
        $firstItemKey = array_key_first($items);
    }
}

?>
-----
<?php

namespace Rector\Tests\Downgrade73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\Fixture;

class SomeClass
{
    public function run($items)
    {
        reset($items);
        $firstItemKey = key($items);
    }
}

?>
