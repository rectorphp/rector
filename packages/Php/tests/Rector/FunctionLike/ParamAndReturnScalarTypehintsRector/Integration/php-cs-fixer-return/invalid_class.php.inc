<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerReturn\InvalidClass;

/** @return \9 */ function my_foo() {}

/** @return \\Foo\\\\Bar */ function my_foo2() {}
