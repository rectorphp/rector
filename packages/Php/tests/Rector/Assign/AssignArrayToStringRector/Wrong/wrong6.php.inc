<?php

function assignArrayToString6()
{
    $array = [1, 2, 3];

    $someString = '';
    foreach ($array as $item) {
        $someString[] = $item;
    }
}

?>
-----
<?php

function assignArrayToString6()
{
    $array = [1, 2, 3];

    $someString = [];
    foreach ($array as $item) {
        $someString[] = $item;
    }
}

?>
