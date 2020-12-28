<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector\Fixture;

class Fixture
{
    public function run(): void
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector\Fixture;

class Fixture
{
    /**
     * @return void
     */
    public function run()
    {
        // do something
    }
}

?>
