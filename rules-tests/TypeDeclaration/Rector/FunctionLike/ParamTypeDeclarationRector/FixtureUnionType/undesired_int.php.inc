<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\FixtureUnionType;

/**
 * @param int $b
 * @param int|void $c
 */
function someFunction($b, $c) {
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\FixtureUnionType;

/**
 * @param int|void $c
 */
function someFunction(int $b, $c) {
}

?>
