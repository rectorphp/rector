<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class BoolClass
{
    /**
     * @return false
     */
    function someFunction($value)
    {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class BoolClass
{
    /**
     * @return false
     */
    function someFunction($value): bool
    {
        return $value;
    }
}

?>
