<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class BoolClass
{
    /**
     * @param true $ojoj
     * @param FALSE $ojoj2
     * @param int|null|true|false $ojoj5
     */
    function someFunction($ojoj, $ojoj2, $ojoj5)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class BoolClass
{
    /**
     * @param int|null|true|false $ojoj5
     */
    function someFunction(bool $ojoj, bool $ojoj2, $ojoj5)
    {
    }
}

?>
