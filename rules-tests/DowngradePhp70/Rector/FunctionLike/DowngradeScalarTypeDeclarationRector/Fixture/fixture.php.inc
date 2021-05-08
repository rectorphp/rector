<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

class Fixture
{
    public function run(string $value)
    {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

class Fixture
{
    /**
     * @param string $value
     */
    public function run($value)
    {
        if (is_object($value)) {
            $value = (string) $value;
        }
        return $value;
    }
}

?>
