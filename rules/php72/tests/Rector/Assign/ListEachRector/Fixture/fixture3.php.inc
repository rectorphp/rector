<?php

namespace Rector\Php72\Tests\Rector\Assign\ListEachRector\Fixture;

function each3()
{
    $flat = book_get_flat_menu($book_link);
    $curr = NULL;
    do {
        $prev = $curr;
        list($key, $curr) = each($flat);
    } while ($key && $key != $book_link['mlid']);
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\Assign\ListEachRector\Fixture;

function each3()
{
    $flat = book_get_flat_menu($book_link);
    $curr = NULL;
    do {
        $prev = $curr;
        $key = key($flat);
        $curr = current($flat);
        next($flat);
    } while ($key && $key != $book_link['mlid']);
}

?>
