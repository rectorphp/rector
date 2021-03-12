<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownFloatParamType
{
    public function getFloatingParam(float $float)
    {
        return $float;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownFloatParamType
{
    public function getFloatingParam(float $float): float
    {
        return $float;
    }
}

?>
