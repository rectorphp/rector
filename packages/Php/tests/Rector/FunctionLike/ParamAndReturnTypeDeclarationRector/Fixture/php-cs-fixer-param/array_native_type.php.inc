<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerParam\ArrayNativeType;

/** @param array $foo */ function my_foo($foo) {}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerParam\ArrayNativeType;

/** @param array $foo */ function my_foo(array $foo) {}
?>
