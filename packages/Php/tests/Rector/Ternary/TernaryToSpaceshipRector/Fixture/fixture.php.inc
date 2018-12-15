<?php

function order_func($a, $b) {
    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);

    return ($a > $b) ? -1 : (($a < $b) ? 1 : 0);
}

?>
-----
<?php

function order_func($a, $b) {
    return $a <=> $b;

    return $b <=> $a;
}

?>
