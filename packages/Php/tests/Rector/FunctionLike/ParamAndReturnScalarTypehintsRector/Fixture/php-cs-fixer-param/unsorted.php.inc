<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerParam\Unsorted;

/**
 * @param int $foo
 * @param string $bar
 * @param Baz $hey
 * @param float $tab
 * @param bool $baz
 */
function my_foo($bar, $foo, $baz, $tab, $hey) {}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerParam\Unsorted;

/**
 * @param int $foo
 * @param string $bar
 * @param Baz $hey
 * @param float $tab
 * @param bool $baz
 */
function my_foo(string $bar, int $foo, bool $baz, float $tab, Baz $hey) {}
?>
