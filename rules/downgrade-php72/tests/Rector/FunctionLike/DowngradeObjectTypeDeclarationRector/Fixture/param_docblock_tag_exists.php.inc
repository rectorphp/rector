<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamDocblockTagExists {
    /**
     * This property is the best one
     * @param object $someObject
     */
    public function someFunction(object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ParamDocblockTagExists {
    /**
     * This property is the best one
     * @param object $someObject
     */
    public function someFunction($someObject)
    {
    }
}

?>
