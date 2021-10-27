<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @param Foo[]|iterable $five
 */
function someFunctionIterableObject($five)
{
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @param Foo[]|iterable $five
 */
function someFunctionIterableObject(iterable $five)
{
}

?>
