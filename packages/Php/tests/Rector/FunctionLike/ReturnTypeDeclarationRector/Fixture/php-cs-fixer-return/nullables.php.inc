<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Nullables;

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

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Nullables;

/** @return null|Bar */
function my_foo($value): ?Bar {
    return $value;
}
/** @return Bar|null */
function my_foo2($value): ?Bar {
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
