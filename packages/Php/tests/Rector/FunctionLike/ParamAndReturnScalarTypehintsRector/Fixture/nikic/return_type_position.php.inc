<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\ReturnTypePosition;

/** @return A */
function test($a = array()) {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\ReturnTypePosition;

/** @return A */
function test($a = array()): A {}

?>
