<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

final class ReturnUnion
{
    public function run(): int|string
    {
        if (mt_rand()) {
            return 1;
        }

        return 'value';
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

final class ReturnUnion
{
    /**
     * @return int|string
     */
    public function run()
    {
        if (mt_rand()) {
            return 1;
        }

        return 'value';
    }
}

?>
