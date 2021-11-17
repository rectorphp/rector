<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

class DocblockTagExists {
    /**
     * Docblock comment.
     * @return never
     */
    public function run(): never
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

class DocblockTagExists {
    /**
     * Docblock comment.
     * @return never
     */
    public function run()
    {
        // do something
    }
}

?>
