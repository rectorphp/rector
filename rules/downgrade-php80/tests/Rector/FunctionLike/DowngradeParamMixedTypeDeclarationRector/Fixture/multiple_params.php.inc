<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector\Fixture;

class MultipleParams
{
    public function someFunction(mixed $anything, string $someOtherVar)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector\Fixture;

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
