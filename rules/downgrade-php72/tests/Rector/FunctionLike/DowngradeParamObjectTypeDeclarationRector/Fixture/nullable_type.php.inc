<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class NullableType
{
    public function someFunction(?object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class NullableType
{
    /**
     * @param object|null $someObject
     */
    public function someFunction($someObject)
    {
    }
}

?>
