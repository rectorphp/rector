<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeTypeDeclarationRector\Fixture;

class Fixture
{
    public function run(string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeTypeDeclarationRector\Fixture;

class Fixture
{
    /**
     * @param string $value
     */
    public function run($value)
    {
    }
}

?>
