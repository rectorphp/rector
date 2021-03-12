<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamNullableType
{
    public function someFunction(?object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamNullableType
{
    /**
     * @param object|null $someObject
     */
    public function someFunction($someObject)
    {
    }
}

?>
