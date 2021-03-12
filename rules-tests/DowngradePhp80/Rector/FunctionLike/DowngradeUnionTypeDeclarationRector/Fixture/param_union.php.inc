<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

final class ParamUnion
{
    public function run(int|string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

final class ParamUnion
{
    /**
     * @param int|string $value
     */
    public function run($value)
    {
    }
}

?>
