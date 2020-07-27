<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class PhpUnitTestCase
{
    /**
     * @var int|null
     */
    private $value;

    public function setUp()
    {
        $this->value = 16236;
    }
}

?>
