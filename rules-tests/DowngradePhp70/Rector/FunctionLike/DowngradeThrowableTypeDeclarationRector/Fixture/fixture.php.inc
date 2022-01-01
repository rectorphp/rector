<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class Fixture
{
    public function someFunction(\Throwable $anything)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class Fixture
{
    /**
     * @param \Throwable $anything
     */
    public function someFunction($anything)
    {
    }
}

?>
