<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownFloatInVariable
{
    public function getFloating()
    {
        $value = 5.3;
        return $value;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownFloatInVariable
{
    public function getFloating(): float
    {
        $value = 5.3;
        return $value;
    }
}

?>
