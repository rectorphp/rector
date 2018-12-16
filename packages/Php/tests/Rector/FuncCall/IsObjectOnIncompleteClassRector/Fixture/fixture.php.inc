<?php

$incompleteObject = new __PHP_Incomplete_Class;
$isObject = is_object($incompleteObject);

$classicObject = new stdClass();
$isObject = is_object($classicObject);

/** @var __PHP_Incomplete_Class $classicObject */
$classicObject = new stdClass();
$isObject = is_object($classicObject);

?>
-----
<?php

$incompleteObject = new __PHP_Incomplete_Class;
$isObject = !is_object($incompleteObject);

$classicObject = new stdClass();
$isObject = is_object($classicObject);

/** @var __PHP_Incomplete_Class $classicObject */
$classicObject = new stdClass();
$isObject = !is_object($classicObject);

?>
