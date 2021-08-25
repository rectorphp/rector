<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector\Fixture;

final class ClosureIterable
{
    public function run()
    {
        return function (): iterable {
            return [];
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector\Fixture;

final class ClosureIterable
{
    public function run()
    {
        return function () {
            return [];
        };
    }
}

?>
