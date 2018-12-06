<?php

function someFunction() {
    $array = [1, 2, 3];

    $someString = '';
    foreach ($array as $item) {
        $someString[] = $item;
    }
}

?>
-----
<?php

function someFunction() {
    $array = [1, 2, 3];

    $someString = [];
    foreach ($array as $item) {
        $someString[] = $item;
    }
}

?>
