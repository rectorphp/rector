<?php

namespace Rector\Php70\Tests\Rector\Assign\ListSwapArrayOrderRector\Fixture;

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

namespace Rector\Php70\Tests\Rector\Assign\ListSwapArrayOrderRector\Fixture;

class MissingListItem
{
    public function b()
    {
        list(, $b[]) = array_reverse([1, 2]);
    }
}

?>
