<?php

namespace Rector\Php70\Tests\Rector\List_\ListSwapArrayOrderRector\Fixture;

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

namespace Rector\Php70\Tests\Rector\List_\ListSwapArrayOrderRector\Fixture;

class ListSwapArrayOrder
{
    public function a()
    {
        list($a[], $a[]) = array_reverse([1, 2]);
    }
}

?>
