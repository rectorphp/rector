<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector\Fixture;

class FunctionParam
{
    public function run(?string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector\Fixture;

class FunctionParam
{
    /**
     * @param string|null $value
     */
    public function run($value)
    {
    }
}

?>
