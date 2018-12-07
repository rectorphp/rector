<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\PhpCsFixerParam\ArrayOfTypes;

/** @param Foo[] $foo */ function my_foo($foo) {}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\PhpCsFixerParam\ArrayOfTypes;

/** @param Foo[] $foo */ function my_foo(array $foo) {}
?>
