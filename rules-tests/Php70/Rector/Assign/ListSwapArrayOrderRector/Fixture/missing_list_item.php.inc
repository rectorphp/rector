<?php

namespace Rector\Tests\Php70\Rector\Assign\ListSwapArrayOrderRector\Fixture;

class MissingListItem
{
    public function b()
    {
        list(, $b[]) = [1, 2];
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\Assign\ListSwapArrayOrderRector\Fixture;

class MissingListItem
{
    public function b()
    {
        list(, $b[]) = array_reverse([1, 2]);
    }
}

?>
