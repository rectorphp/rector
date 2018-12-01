<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerParam\CallableType;

/** @param callable $foo */ function my_foo($foo) {}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerParam\CallableType;

/** @param callable $foo */ function my_foo(callable $foo) {}
?>
