<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\Fixture;

class NullableType
{
    public function getSomeObject(): ?object
    {
        return new SomeObject();
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\Fixture;

class NullableType
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
