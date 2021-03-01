<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

final class ParamUnion
{
    public function run(int|string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

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
