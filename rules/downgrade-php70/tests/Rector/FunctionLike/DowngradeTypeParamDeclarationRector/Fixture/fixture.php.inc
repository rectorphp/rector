<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeParamDeclarationRector\Fixture;

class FunctionParam
{
    public function run(string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeParamDeclarationRector\Fixture;

class FunctionParam
{
    /**
     * @param string $value
     */
    public function run($value)
    {
    }
}

?>
