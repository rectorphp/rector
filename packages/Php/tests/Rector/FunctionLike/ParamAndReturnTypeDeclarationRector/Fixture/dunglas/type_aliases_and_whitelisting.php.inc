<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\Dunglas;

/**
 * @param integer $integer
 * @param boolean $boolean
 * @param real $real
 * @param double $double
 * @param callback $callback
 * @param void $void
 * @param mixed $mixed
 * @param unknown $unknown
 * @param Class $class
 */
function aliases($integer, $boolean, $real, $double, $callback, $void, $mixed, $unkown, $class) {
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\Dunglas;

/**
 * @param integer $integer
 * @param boolean $boolean
 * @param real $real
 * @param double $double
 * @param callback $callback
 * @param void $void
 * @param mixed $mixed
 * @param unknown $unknown
 * @param Class $class
 */
function aliases(int $integer, bool $boolean, float $real, float $double, callable $callback, $void, $mixed, $unkown, Class $class) {
}
?>
