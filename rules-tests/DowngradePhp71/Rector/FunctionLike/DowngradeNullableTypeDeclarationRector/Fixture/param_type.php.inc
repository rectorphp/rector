<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\Fixture;

final class ParamType
{
    public function run(?string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\Fixture;

final class ParamType
{
    /**
     * @param string|null $value
     */
    public function run($value)
    {
    }
}

?>
