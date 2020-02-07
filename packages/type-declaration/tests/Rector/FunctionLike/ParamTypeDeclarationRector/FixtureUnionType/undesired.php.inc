<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\FixtureUnionType;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\FixtureUnionType;

/**
 * @param real|int $a
 * @param int $b
 * @param int|void $c
 */
function someFunction(float|int $a, int $b, int|void $c) {
}

?>
