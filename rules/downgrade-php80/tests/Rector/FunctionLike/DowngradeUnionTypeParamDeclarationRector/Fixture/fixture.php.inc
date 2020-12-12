<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector\Fixture;

class Fixture
{
    public function run(int|string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector\Fixture;

class Fixture
{
    /**
     * @param int|string $value
     */
    public function run($value)
    {
    }
}

?>
