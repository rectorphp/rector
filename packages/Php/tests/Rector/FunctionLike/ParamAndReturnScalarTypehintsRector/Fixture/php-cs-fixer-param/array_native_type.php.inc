<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\PhpCsFixerParam\ArrayNativeType;

/** @param array $foo */ function my_foo($foo) {}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\PhpCsFixerParam\ArrayNativeType;

/** @param array $foo */ function my_foo(array $foo) {}
?>
