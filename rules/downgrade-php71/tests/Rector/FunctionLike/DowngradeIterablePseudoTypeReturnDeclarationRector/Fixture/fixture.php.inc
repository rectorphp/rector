<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeReturnDeclarationRector\Fixture;

class SomeClass
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

class SomeClass
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
