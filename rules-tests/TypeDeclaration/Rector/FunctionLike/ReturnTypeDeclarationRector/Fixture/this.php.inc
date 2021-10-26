<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class ThisClass
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

final class ThisClass
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
