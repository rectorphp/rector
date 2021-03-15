<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class ThisClass
{
    /**
     * @return $this
     */
    function someFunction($param, $param2)
    {
        return $param;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class ThisClass
{
    /**
     * @return $this
     */
    function someFunction($param, $param2): self
    {
        return $param;
    }
}

?>
