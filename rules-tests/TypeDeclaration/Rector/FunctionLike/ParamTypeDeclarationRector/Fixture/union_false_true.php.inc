<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

final class UnionFalseTrue
{
    /**
     * @param true|false $ojoj3
     */
    function someFunction($ojoj3)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

final class UnionFalseTrue
{
    function someFunction(bool $ojoj3)
    {
    }
}

?>
