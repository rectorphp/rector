<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     */
    public function run(): void
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector\Fixture;

class DocblockExists {
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
