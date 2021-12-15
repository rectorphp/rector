<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\FixturePhp73;

final class ConstructorAssign
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

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\FixturePhp73;

final class ConstructorAssign
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
