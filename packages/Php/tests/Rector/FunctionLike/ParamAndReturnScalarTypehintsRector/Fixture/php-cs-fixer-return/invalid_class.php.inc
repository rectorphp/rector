<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\PhpCsFixerReturn\InvalidClass;

/** @return \9 */ function my_foo() {}

/** @return \\Foo\\\\Bar */ function my_foo2() {}
