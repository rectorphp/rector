<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ReturnDocblockExists {
    /**
     * This property is the best one
     */
    public function getSomeObject(): object
    {
        return new SomeObject();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ReturnDocblockExists {
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
