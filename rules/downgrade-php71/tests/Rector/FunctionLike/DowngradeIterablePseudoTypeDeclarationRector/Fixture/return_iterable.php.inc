<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector\Fixture;

final class ReturnIterable
{
    public function run(): iterable
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector\Fixture;

final class ReturnIterable
{
    /**
     * @return mixed[]
     */
    public function run()
    {
        // do something
    }
}

?>
