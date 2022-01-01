<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     */
    public function someFunction(\Throwable $anything)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     * @param \Throwable $anything
     */
    public function someFunction($anything)
    {
    }
}

?>
