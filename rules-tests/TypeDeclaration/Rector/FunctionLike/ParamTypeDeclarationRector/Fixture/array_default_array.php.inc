<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class ArrayDefaultArray
{
    /**
     * @param array $value
     */
    public function someFunction($value = [])
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class ArrayDefaultArray
{
    public function someFunction(array $value = [])
    {
    }
}

?>
