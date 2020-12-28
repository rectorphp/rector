<?php

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeReturnDeclarationRector\Fixture;

class Fixture
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

class Fixture
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
