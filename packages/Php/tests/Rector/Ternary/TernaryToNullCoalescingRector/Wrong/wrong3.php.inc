<?php

function ternaryToNullCoalescing3()
{
    $f = $g === null ? 'g' : $g;

    $ff = null === $gg ? 'gg' : $gg;
}

?>
-----
<?php

function ternaryToNullCoalescing3()
{
    $f = $g ?? 'g';

    $ff = $gg ?? 'gg';
}

?>
