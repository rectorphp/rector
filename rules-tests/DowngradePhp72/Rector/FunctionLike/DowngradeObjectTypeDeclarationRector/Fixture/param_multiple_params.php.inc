<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamMultipleParams
{
    public function someFunction(object $someObject, string $someOtherVar)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamMultipleParams
{
    /**
     * @param object $someObject
     */
    public function someFunction($someObject, string $someOtherVar)
    {
    }
}

?>
