<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeReturnDeclarationRector\Fixture;

class Array
{
    public function run(): array
    {
        return [1, 2, 3];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeReturnDeclarationRector\Fixture;

class Array
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
