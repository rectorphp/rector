<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class ConstructorAssign
{
    private $result;
    private $name;

    public function __construct(int $value)
    {
        $this->result = 5 + $value;
        $this->name = 'Tomas';
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class ConstructorAssign
{
    /**
     * @var int
     */
    private $result;
    /**
     * @var string
     */
    private $name;

    public function __construct(int $value)
    {
        $this->result = 5 + $value;
        $this->name = 'Tomas';
    }
}

?>
