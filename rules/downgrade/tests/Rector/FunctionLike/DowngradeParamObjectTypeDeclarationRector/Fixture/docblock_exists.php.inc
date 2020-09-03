<?php

namespace Rector\Downgrade\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class DocBlockExists {
    /**
     * This property is the best one
     */
    public function someFunction(object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\Fixture;

class DocBlockExists {
    /**
     * This property is the best one
     * @param object $someObject
     */
    public function someFunction($someObject)
    {
    }
}

?>
