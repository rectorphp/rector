<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeDeclarationRector\Fixture;

class ReturnArrayTypeOnClassMethod
{
    public function run(): array
    {
        return [1, 2, 3];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeDeclarationRector\Fixture;

class ReturnArrayTypeOnClassMethod
{
    /**
     * @return mixed[]
     */
    public function run()
    {
        return [1, 2, 3];
    }
}

?>
