<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class MultipleParams
{
    public function someFunction(mixed $anything, string $someOtherVar)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class MultipleParams
{
    /**
     * @param mixed $anything
     */
    public function someFunction($anything, string $someOtherVar)
    {
    }
}

?>
