<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeParamDeclarationRector\Fixture;

class Fixture
{
    public function run(iterable $iterator)
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeParamDeclarationRector\Fixture;

class Fixture
{
    /**
     * @param mixed[]|\Traversable $iterator
     */
    public function run($iterator)
    {
        // do something
    }
}

?>
