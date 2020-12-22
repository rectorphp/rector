<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class Fixture
{
    public function someFunction(object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class Fixture
{
    /**
     * @param object $someObject
     */
    public function someFunction($someObject)
    {
    }
}

?>
