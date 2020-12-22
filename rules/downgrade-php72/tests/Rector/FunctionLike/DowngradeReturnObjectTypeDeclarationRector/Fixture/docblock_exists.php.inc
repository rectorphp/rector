<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\Fixture;

class DocblockExists {
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

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\Fixture;

class DocblockExists {
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
