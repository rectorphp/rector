<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

final class ReturnFixture
{
    public function getAnything(): \Throwable
    {
        return new \Exception('Yikes!');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

final class ReturnFixture
{
    /**
     * @return \Throwable
     */
    public function getAnything()
    {
        return new \Exception('Yikes!');
    }
}

?>
