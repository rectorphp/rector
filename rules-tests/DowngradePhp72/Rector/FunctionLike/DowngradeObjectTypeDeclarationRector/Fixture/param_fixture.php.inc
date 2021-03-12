<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamFixture
{
    public function someFunction(object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

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
