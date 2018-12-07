<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\PhpCsFixerReturn\Arrays;

/** @return array<int, bool> */
function my_foo6() {}

/** @return Foo[][] */
function my_foo7() {}

/** @return Foo[] */ function my_foo() {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\PhpCsFixerReturn\Arrays;

/** @return array<int, bool> */
function my_foo6(): array {}

/** @return Foo[][] */
function my_foo7(): array {}

/** @return Foo[] */ function my_foo(): array {}

?>
