<?php

function mirrorFunction()
{
    $a = $a;

    if (true) {
        $b = 1;
        $b = $b;
        $c = $b;
    }
}

?>
-----
<?php

function mirrorFunction()
{
    if (true) {
        $b = 1;
        $c = $b;
    }
}

?>
