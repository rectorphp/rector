<?php

/** @return void */
function test111() {}

/** @return mixed */
function test222() {}

/** @return static */
function test333() {}

?>
-----
<?php

/** @return void */
function test111(): void {}

/** @return mixed */
function test222() {}

/** @return static */
function test333() {}

?>
