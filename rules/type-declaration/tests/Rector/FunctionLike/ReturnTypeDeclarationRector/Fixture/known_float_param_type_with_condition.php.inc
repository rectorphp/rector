<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownFloatParamTypeWithCondition
{
    public function getFloatingTanks(float $float)
    {
        if (true) {
            return $float;
        }

        return 5.0;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownFloatParamTypeWithCondition
{
    public function getFloatingTanks(float $float): float
    {
        if (true) {
            return $float;
        }

        return 5.0;
    }
}

?>
