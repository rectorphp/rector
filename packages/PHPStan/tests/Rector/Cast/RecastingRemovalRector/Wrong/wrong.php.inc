<?php

$string = '';
$string = (string) $string;

$array = [];
$array = (array) $array;

$array = (array) $string;

?>
-----
<?php

$string = '';
$string = $string;

$array = [];
$array = $array;

$array = (array) $string;

?>
