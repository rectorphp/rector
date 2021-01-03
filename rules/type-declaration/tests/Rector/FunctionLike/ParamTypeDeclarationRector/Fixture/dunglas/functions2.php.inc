<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\Dunglas;

function myFunction($param = false)
{
}
/**
 * @param string $param
 */
function myOtherFunction($param)
{
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\Dunglas;

function myFunction($param = false)
{
}
/**
 * @param string $param
 */
function myOtherFunction(string $param)
{
}
?>
