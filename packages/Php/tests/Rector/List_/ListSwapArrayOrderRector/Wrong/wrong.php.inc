<?php

namespace Rector\Php\Tests\Rector\List_\ListSwapArrayOrderRector\Wrong;

function listSwapArrayOrder()
{
    list($a[], $a[]) = [1, 2];

    list($a[], $b[]) = [1, 2];

    list($a, $b, $c) = [1, 2, 3];
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\List_\ListSwapArrayOrderRector\Wrong;

function listSwapArrayOrder()
{
    list($a[], $a[]) = array_reverse([1, 2]);

    list($a[], $b[]) = [1, 2];

    list($a, $b, $c) = [1, 2, 3];
}

?>
