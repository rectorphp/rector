<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\ReturnTypePosition;

/** @return A */
function test($a = array()) {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\ReturnTypePosition;

/** @return A */
function test($a = array()): A {}

?>
