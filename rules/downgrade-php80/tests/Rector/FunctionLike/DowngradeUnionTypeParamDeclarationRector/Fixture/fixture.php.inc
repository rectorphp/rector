<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector\Fixture;

class FunctionParam
{
    public function run(int|string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector\Fixture;

class FunctionParam
{
    /**
     * @param int|string $value
     */
    public function run($value)
    {
    }
}

?>
