<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector\Fixture;

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

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector\Fixture;

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
