<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeTypeDeclarationRector\Fixture;

final class ReturnString
{
    public function run(): string
    {
        return 'Hello world';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeTypeDeclarationRector\Fixture;

final class ReturnString
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
