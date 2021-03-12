<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

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

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

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
