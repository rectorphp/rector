<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerParam\ArrayOfTypes;

/** @param Foo[] $foo */ function my_foo($foo) {}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerParam\ArrayOfTypes;

/** @param Foo[] $foo */ function my_foo(array $foo) {}
?>
