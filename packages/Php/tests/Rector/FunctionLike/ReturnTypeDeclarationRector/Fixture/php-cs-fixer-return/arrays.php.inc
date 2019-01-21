<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Arrays;

/** @return array<int, bool> */
function my_foo6($value) {
    return $value;
}

/** @return Foo[][] */
function my_foo7($value) {
    return $value;
}

/** @return Foo[] */ function my_foo($value) {
    return $value;
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Arrays;

/** @return array<int, bool> */
function my_foo6($value): array {
    return $value;
}

/** @return Foo[][] */
function my_foo7($value): array {
    return $value;
}

/** @return Foo[] */ function my_foo($value): array {
    return $value;
}

?>
