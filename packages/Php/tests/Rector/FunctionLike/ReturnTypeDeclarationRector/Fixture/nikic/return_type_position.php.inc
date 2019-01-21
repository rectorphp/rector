<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\ReturnTypePosition;

/** @return A */
function test($a = array()) {
    return $a;
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\ReturnTypePosition;

/** @return A */
function test($a = array()): A {
    return $a;
}

?>
