<?php

namespace Rector\Downgrade\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\Fixture;

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

namespace Rector\Downgrade\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\Fixture;

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
