<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class PhpUnitTestCase
{
    private $value;

    public function setUp()
    {
        $this->value = 16236;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class PhpUnitTestCase
{
    /**
     * @var int
     */
    private $value;

    public function setUp()
    {
        $this->value = 16236;
    }
}

?>
