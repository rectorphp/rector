<?php declare(strict_types=1);

function someFunction() {
    $array = [1, 2, 3];

    $someString = '';
    foreach ($array as $item) {
        $someString[] = $item;
    }
}
