<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Undesired;

/**
 * @param real|int $a
 * @param int $b
 * @param int|void $c
 */
function someFunction($a, $b, $c) {
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Undesired;

/**
 * @param real|int $a
 * @param int $b
 * @param int|void $c
 */
function someFunction($a, int $b, $c) {
}
?>
