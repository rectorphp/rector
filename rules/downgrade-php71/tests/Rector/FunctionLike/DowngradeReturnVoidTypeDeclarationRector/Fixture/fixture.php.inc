<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeReturnVoidTypeDeclarationRector\Fixture;

class SomeClass
{
    public function run(): void
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeReturnVoidTypeDeclarationRector\Fixture;

class SomeClass
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
