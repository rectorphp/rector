<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeTypeDeclarationRector\Fixture;

class ReturnArray
{
    public function run(): array
    {
        return [1, 2, 3];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeTypeDeclarationRector\Fixture;

class ReturnArray
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
