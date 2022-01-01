<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class MultipleParams
{
    public function someFunction(\Throwable $anything, string $someOtherVar)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class MultipleParams
{
    /**
     * @param \Throwable $anything
     */
    public function someFunction($anything, string $someOtherVar)
    {
    }
}

?>
