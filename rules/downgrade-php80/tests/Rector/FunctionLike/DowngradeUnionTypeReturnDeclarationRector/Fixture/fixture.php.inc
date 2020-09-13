<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector\Fixture;

class ReturnOnClassMethod
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

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector\Fixture;

class ReturnOnClassMethod
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
