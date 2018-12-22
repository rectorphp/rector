<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Skip;

/** @return resource */
function my_foo() {}

/** @return mixed */
function my_foo2() {}

/** @return null */
function my_foo3() {}

/** @return Foo|Bar */
function my_foo4() {}

/** @return null|Foo|Bar */
function my_foo5() {}
