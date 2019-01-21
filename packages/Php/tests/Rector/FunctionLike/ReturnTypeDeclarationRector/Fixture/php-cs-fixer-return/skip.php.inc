<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Skip;

/** @return resource */
function my_foo($value) {
    return $value;
}

/** @return mixed */
function my_foo2($value) {
    return $value;
}

/** @return null */
function my_foo3($value) {
    return $value;
}

/** @return Foo|Bar */
function my_foo4($value) {
    return $value;
}

/** @return null|Foo|Bar */
function my_foo5($value) {
    return $value;
}
