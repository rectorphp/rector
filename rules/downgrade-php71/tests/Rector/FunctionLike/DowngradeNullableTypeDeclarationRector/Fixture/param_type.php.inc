<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\Fixture;

final class ParamType
{
    public function run(?string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\Fixture;

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
