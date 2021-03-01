<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

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

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

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
