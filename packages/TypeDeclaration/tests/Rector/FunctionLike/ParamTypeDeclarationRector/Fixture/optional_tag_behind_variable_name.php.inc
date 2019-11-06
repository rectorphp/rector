<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * @param string $string[optional]
 */
function test($string = null){

}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * @param string $string[optional]
 */
function test(string $string = null){

}

?>

