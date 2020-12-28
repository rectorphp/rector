<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector\Fixture;

class Fixture
{
    public function run(?string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector\Fixture;

class Fixture
{
    /**
     * @param string|null $value
     */
    public function run($value)
    {
    }
}

?>
