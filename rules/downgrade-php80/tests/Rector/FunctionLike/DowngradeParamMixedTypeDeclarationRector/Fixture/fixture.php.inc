<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector\Fixture;

class Fixture
{
    public function someFunction(mixed $anything)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector\Fixture;

class Fixture
{
    /**
     * @param mixed $anything
     */
    public function someFunction($anything)
    {
    }
}

?>
