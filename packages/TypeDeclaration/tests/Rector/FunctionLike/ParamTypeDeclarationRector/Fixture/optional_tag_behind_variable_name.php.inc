<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * @var string $string[optional]
 */
function test($string = null){

}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * @var string $string[optional]
 */
function test(string $string = null){

}

?>

