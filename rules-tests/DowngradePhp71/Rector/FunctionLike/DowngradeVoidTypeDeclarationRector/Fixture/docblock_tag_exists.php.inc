<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector\Fixture;

class DocblockTagExists {
    /**
     * This property is the best one
     * @return void
     */
    public function run(): void
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector\Fixture;

class DocblockTagExists {
    /**
     * This property is the best one
     * @return void
     */
    public function run()
    {
        // do something
    }
}

?>
