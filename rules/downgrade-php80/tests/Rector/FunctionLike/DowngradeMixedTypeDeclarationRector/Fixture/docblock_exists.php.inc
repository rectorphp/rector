<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     */
    public function someFunction(mixed $anything)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     * @param mixed $anything
     */
    public function someFunction($anything)
    {
    }
}

?>
