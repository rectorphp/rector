<?php

$object = new stdClass();
$object = (object) $object;
$object = (array) $object;

$int = 5;
$float = (float) $int;
$int = (int) $int;

?>
-----
<?php

$object = new stdClass();
$object = $object;
$object = (array) $object;

$int = 5;
$float = (float) $int;
$int = $int;

?>
