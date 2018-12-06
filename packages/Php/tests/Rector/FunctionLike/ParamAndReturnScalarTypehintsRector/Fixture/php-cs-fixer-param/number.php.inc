<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerParam\Number;

/** @param float $bar */ function my_foo($bar) {}

/** @param int $bar */ function my_foo2($bar) {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerParam\Number;

/** @param float $bar */ function my_foo(float $bar) {}

/** @param int $bar */ function my_foo2(int $bar) {}

?>
