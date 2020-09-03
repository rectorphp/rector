<?php

namespace Rector\Downgrade\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class DocBlockTagExists {
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

namespace Rector\Downgrade\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class DocBlockTagExists {
    /**
     * This property is the best one
     * @param object $someObject
     */
    public function someFunction($someObject)
    {
    }
}

?>
