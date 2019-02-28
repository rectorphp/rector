<?php declare(strict_types=1);

$items = [1, 2, 3];
foreach ($items as $item) {
    $item *= 2;
    echo '<strong>' . $item . '</strong>';
}
