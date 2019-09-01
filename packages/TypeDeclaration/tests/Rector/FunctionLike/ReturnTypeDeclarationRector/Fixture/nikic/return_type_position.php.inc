<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\ReturnTypePosition;

/** @return A */
function test($a = array()) {
    return $a;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\ReturnTypePosition;

/** @return A */
function test($a = array()): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\ReturnTypePosition\A {
    return $a;
}

?>
