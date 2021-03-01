<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ReturnDocblockTagExists {
    /**
     * This property is the best one
     * @return object
     */
    public function getSomeObject(): object
    {
        return new SomeObject();
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ReturnDocblockTagExists {
    /**
     * This property is the best one
     * @return object
     */
    public function getSomeObject()
    {
        return new SomeObject();
    }
}

?>
