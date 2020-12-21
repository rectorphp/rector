<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\Fixture;

class Fixture
{
    public function getSomeObject(): object
    {
        return new SomeObject();
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\Fixture;

class Fixture
{
    /**
     * @return object
     */
    public function getSomeObject()
    {
        return new SomeObject();
    }
}

?>
