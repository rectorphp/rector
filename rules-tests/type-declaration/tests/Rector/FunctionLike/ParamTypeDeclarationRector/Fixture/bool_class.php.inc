<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class BoolClass
{
    /**
     * @param true $ojoj
     * @param FALSE $ojoj2
     * @param true|false $ojoj3
     * @param int|null|true|false $ojoj5
     */
    function someFunction($ojoj, $ojoj2, $ojoj3, $ojoj5)
    {
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class BoolClass
{
    /**
     * @param int|null|true|false $ojoj5
     */
    function someFunction(bool $ojoj, bool $ojoj2, bool $ojoj3, $ojoj5)
    {
    }
}

?>
