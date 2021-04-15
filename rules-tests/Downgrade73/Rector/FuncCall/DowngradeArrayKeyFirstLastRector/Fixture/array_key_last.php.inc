<?php

namespace Rector\Tests\Downgrade73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\Fixture;

class SomeClass
{
    public function run($items)
    {
        $lastItemKey = array_key_last($items);
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
        end($items);
        $lastItemKey = key($items);
    }
}

?>
