<?php

namespace Rector\Php72\Tests\Rector\FuncCall\IsObjectOnIncompleteClassRector\Fixture;

use __PHP_Incomplete_Class;
use stdClass;

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

namespace Rector\Php72\Tests\Rector\FuncCall\IsObjectOnIncompleteClassRector\Fixture;

use __PHP_Incomplete_Class;
use stdClass;

$incompleteObject = new __PHP_Incomplete_Class;
$isObject = !is_object($incompleteObject);

$classicObject = new stdClass();
$isObject = is_object($classicObject);

/** @var __PHP_Incomplete_Class $classicObject */
$classicObject = new stdClass();
$isObject = !is_object($classicObject);

?>
