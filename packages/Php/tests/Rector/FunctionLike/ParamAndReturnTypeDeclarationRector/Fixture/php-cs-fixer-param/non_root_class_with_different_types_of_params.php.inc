<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerParam\TypehintAlreadyDefinedWithWrongPhpdocTypehint;

/**
 * @param string $bar
 * @param int $baz
 * @param float $tab
 */
function my_foo($bar, $baz, $tab) {}

/** @param My\Bar $foo */ function my_foo2($foo) {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerParam\TypehintAlreadyDefinedWithWrongPhpdocTypehint;

/**
 * @param string $bar
 * @param int $baz
 * @param float $tab
 */
function my_foo(string $bar, int $baz, float $tab) {}

/** @param My\Bar $foo */ function my_foo2(My\Bar $foo) {}

?>
