<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class DocblockTagExists {
    /**
     * This property is the best one
     * @param \Throwable $anything
     */
    public function someFunction(\Throwable $anything)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class DocblockTagExists {
    /**
     * This property is the best one
     * @param \Throwable $anything
     */
    public function someFunction($anything)
    {
    }
}

?>
