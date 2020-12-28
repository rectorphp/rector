<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeReturnDeclarationRector\Fixture;

class Fixture
{
    public function run(): iterable
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeReturnDeclarationRector\Fixture;

class Fixture
{
    /**
     * @return mixed[]|\Traversable
     */
    public function run()
    {
        // do something
    }
}

?>
