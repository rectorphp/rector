<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class Fixture
{
    public function someFunction(mixed $anything)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

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
