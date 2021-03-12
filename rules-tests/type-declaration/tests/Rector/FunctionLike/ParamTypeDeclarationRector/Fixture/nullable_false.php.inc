<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class NullableFalse
{
    /**
     * @param true|false|null $ojoj3
     * @param null|BOOL|true|false $ojoj6
     */
    function someFunction($ojoj3, $ojoj6)
    {
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class NullableFalse
{
    function someFunction(?bool $ojoj3, ?bool $ojoj6)
    {
    }
}

?>
