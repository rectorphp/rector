<?php

function run($val1, $val2, $val3, $val4)
{
    if (
    (
        1 === $val1 and
        1 === $val2 and
        1 === $val3 and
        1 === $val4
    )
    ) {
        //code
    }
}

?>
-----
<?php

function run($val1, $val2, $val3, $val4)
{
    if (
    (
        1 === $val1 && 1 === $val2 && 1 === $val3 && 1 === $val4
    )
    ) {
        //code
    }
}

?>
