<?php

function getRes(): string
{
    if ('a' !== 'b') {
        $res = "a";
    } else {
        $res = "b";
    }

    return $res;
}
?>
-----
<?php

function getRes(): string
{
    $res = 'a' !== 'b' ? "a" : "b";

    return $res;
}
?>
