<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ReturnNullableType
{
    public function getSomeObject(): ?object
    {
        return new SomeObject();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class ReturnNullableType
{
    /**
     * @return object|null
     */
    public function getSomeObject()
    {
        return new SomeObject();
    }
}

?>
