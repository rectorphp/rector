<?php declare(strict_types=1);

$flat = book_get_flat_menu($book_link);
$curr = NULL;
do {
    $prev = $curr;
    list($key, $curr) = each($flat);
} while ($key && $key != $book_link['mlid']);
