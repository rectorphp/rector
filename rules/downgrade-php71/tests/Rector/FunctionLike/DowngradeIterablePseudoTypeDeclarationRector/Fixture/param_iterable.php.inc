<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector\Fixture;

final class ParamIterable
{
    public function run(iterable $iterator)
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector\Fixture;

final class ParamIterable
{
    /**
     * @param mixed[] $iterator
     */
    public function run($iterator)
    {
        // do something
    }
}

?>
