<?php declare(strict_types=1);

$flat = book_get_flat_menu($book_link);
$curr = NULL;
do {
    $prev = $curr;
    $key = key($flat);
    $curr = current($flat);
    next($flat);
} while ($key && $key != $book_link['mlid']);
