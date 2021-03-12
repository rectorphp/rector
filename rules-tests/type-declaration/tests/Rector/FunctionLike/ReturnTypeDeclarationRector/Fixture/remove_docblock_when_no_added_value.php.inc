<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class RemoveWhenNoAddedValue
{
    /**
     * @return float
     */
    public function getSize()
    {
        return 10.5;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class RemoveWhenNoAddedValue
{
    public function getSize(): float
    {
        return 10.5;
    }
}

?>
