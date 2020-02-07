<?php

if ($f = false or true) {
    return $f;
}

if (false or true) {
    return $f;
}

?>
-----
<?php

if (($f = false) || true) {
    return $f;
}

if (false || true) {
    return $f;
}

?>
