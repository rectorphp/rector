<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class CodeOverDocPriority
{
    /**
     * @return bool
     */
    public function getNumber()
    {
        return 5;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class CodeOverDocPriority
{
    public function getNumber(): int
    {
        return 5;
    }
}

?>
