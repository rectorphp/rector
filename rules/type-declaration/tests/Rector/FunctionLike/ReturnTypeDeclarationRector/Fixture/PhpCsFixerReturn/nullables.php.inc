<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Nullables;

class Bar {}
class Foo {}

/** @return null|Bar */
function my_foo($value) {
    return $value;
}
/** @return Bar|null */
function my_foo2($value) {
    return $value;
}
/** @return null|array */
function my_foo3($value) {
    return $value;
}
/** @return null|Foo[] */
function my_foo4($value) {
    return $value;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Nullables;

class Bar {}
class Foo {}

function my_foo($value): ?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Nullables\Bar {
    return $value;
}
function my_foo2($value): ?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Nullables\Bar {
    return $value;
}
/** @return null|array */
function my_foo3($value): ?array {
    return $value;
}
/** @return null|Foo[] */
function my_foo4($value): ?array {
    return $value;
}

?>
