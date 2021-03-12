<?php

namespace Rector\Tests\Php70\Rector\Assign\ListSwapArrayOrderRector\Fixture;

class Fixture
{
    public function a()
    {
        list($a[], $a[]) = [1, 2];
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\Assign\ListSwapArrayOrderRector\Fixture;

class Fixture
{
    public function a()
    {
        list($a[], $a[]) = array_reverse([1, 2]);
    }
}

?>
