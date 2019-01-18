<?php

namespace Rector\Php\Tests\Rector\List_\ListSwapArrayOrderRector\Fixture;

class ListSwapArrayOrder
{
    public function a()
    {
        list($a[], $a[]) = [1, 2];
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\List_\ListSwapArrayOrderRector\Fixture;

class ListSwapArrayOrder
{
    public function a()
    {
        list($a[], $a[]) = array_reverse([1, 2]);
    }
}

?>
