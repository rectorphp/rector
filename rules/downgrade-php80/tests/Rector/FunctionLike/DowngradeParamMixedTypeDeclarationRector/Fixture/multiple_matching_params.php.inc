<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector\Fixture;

class MultipleMatchingParams
{
    public function someFunction(mixed $anything, string $someOtherVar, mixed $someOtherObject)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector\Fixture;

class MultipleMatchingParams
{
    /**
     * @param mixed $anything
     * @param mixed $someOtherObject
     */
    public function someFunction($anything, string $someOtherVar, $someOtherObject)
    {
    }
}

?>
