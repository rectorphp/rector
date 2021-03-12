<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

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

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownFloatParamType
{
    public function getFloatingParam(float $float): float
    {
        return $float;
    }
}

?>
