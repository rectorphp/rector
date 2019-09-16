<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Dunglas;

class AnyClass
{}

/**
 * @param integer $integer
 * @param boolean $boolean
 * @param real $real
 * @param double $double
 * @param callback $callback
 * @param void $void
 * @param mixed $mixed
 * @param unknown $unknown
 * @param AnyClass $class
 */
function aliases($integer, $boolean, $real, $double, $callback, $void, $mixed, $unkown, $class) {
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Dunglas;

class AnyClass
{}

/**
 * @param integer $integer
 * @param boolean $boolean
 * @param real $real
 * @param double $double
 * @param callback $callback
 * @param void $void
 * @param mixed $mixed
 * @param unknown $unknown
 * @param AnyClass $class
 */
function aliases(int $integer, bool $boolean, float $real, float $double, callable $callback, $void, $mixed, $unkown, \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Dunglas\AnyClass $class) {
}
?>
