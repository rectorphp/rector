<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeReturnVoidTypeDeclarationRector\Fixture;

class DocBlockExists {
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

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeReturnVoidTypeDeclarationRector\Fixture;

class DocBlockExists {
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
