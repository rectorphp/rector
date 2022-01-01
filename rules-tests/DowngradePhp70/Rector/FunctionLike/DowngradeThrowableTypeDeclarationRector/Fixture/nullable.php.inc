<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class Fixture
{
    public function someFunction(?\Throwable $anything): ?\Throwable
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
     * @param \Throwable|null $anything
     * @return \Throwable|null
     */
    public function someFunction($anything)
    {
    }
}

?>
