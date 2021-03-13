<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\Undesired;

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

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\Undesired;

/**
 * @param real|int $a
 * @param int|void $c
 */
function someFunction($a, int $b, $c) {
}

?>
