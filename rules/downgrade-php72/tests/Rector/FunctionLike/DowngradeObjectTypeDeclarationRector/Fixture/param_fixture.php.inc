<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class Fixture
{
    public function someFunction(object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

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
