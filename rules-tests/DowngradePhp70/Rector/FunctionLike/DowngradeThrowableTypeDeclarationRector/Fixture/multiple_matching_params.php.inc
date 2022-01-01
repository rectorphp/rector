<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class MultipleMatchingParams
{
    public function someFunction(\Throwable $anything, string $someOtherVar, \Throwable $someOtherObject)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class MultipleMatchingParams
{
    /**
     * @param \Throwable $anything
     * @param \Throwable $someOtherObject
     */
    public function someFunction($anything, string $someOtherVar, $someOtherObject)
    {
    }
}

?>
