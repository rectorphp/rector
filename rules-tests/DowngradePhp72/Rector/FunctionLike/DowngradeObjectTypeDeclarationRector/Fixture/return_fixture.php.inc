<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ReturnFixture
{
    public function getSomeObject(): object
    {
        return new SomeObject();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ReturnFixture
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
