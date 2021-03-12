<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamFixture
{
    public function someFunction(object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamFixture
{
    /**
     * @param object $someObject
     */
    public function someFunction($someObject)
    {
    }
}

?>
