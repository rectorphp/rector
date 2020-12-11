<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeReturnDeclarationRector\Fixture;

class ReturnTypeOnClassMethod
{
    public function run(): string
    {
        return 'Hello world';
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeReturnDeclarationRector\Fixture;

class ReturnTypeOnClassMethod
{
    /**
     * @return string
     */
    public function run()
    {
        return 'Hello world';
    }
}

?>
