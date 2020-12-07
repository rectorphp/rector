<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeParamDeclarationRector\Fixture;

class ArrayFunctionParam
{
    public function run(array $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeParamDeclarationRector\Fixture;

class ArrayFunctionParam
{
    /**
     * @param mixed[] $value
     */
    public function run($value)
    {
    }
}

?>
