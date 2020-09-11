<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeReturnDeclarationRector\Fixture;

class ClassMethod
{
    public function run(): ?string
    {
        if (mt_rand()) {
            return null;
        }

        return 'value';
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeReturnDeclarationRector\Fixture;

class ClassMethod
{
    /**
     * @return string|null
     */
    public function run()
    {
        if (mt_rand()) {
            return null;
        }

        return 'value';
    }
}

?>
