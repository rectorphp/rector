<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class MultipleMatchingParams
{
    public function someFunction(mixed $anything, string $someOtherVar, mixed $someOtherObject)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

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
