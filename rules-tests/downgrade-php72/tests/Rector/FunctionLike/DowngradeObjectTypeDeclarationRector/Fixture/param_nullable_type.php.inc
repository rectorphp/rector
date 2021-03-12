<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamNullableType
{
    public function someFunction(?object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

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
