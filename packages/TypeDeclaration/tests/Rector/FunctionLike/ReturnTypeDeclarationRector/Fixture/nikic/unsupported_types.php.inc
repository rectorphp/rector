<?php

/** @return void */
function test111($value) {
    return;
}

/** @return mixed */
function test222($value) {
    return $value;
}

/** @return static */
function test333($value) {
    return $value;
}

?>
-----
<?php

/** @return void */
function test111($value): void {
    return;
}

/** @return mixed */
function test222($value) {
    return $value;
}

/** @return static */
function test333($value) {
    return $value;
}

?>
