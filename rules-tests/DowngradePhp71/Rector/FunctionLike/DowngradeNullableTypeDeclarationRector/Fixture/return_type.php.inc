<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\Fixture;

final class ReturnType
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

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\Fixture;

final class ReturnType
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
