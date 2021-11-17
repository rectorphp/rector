<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

class DocblockExists {
    /**
     * Docblock comment.
     */
    public function run(): never
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

class DocblockExists {
    /**
     * Docblock comment.
     * @return never
     */
    public function run()
    {
    }
}

?>
