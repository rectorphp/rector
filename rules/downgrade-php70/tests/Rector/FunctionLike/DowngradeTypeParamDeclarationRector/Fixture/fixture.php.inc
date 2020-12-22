<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeParamDeclarationRector\Fixture;

class Fixture
{
    public function run(string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeParamDeclarationRector\Fixture;

class Fixture
{
    /**
     * @param string $value
     */
    public function run($value)
    {
    }
}

?>
