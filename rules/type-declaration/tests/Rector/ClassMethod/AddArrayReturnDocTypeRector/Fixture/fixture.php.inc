<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class Fixture
{
    /**
     * @var int[]
     */
    private $values;

    public function getValues(): array
    {
        return $this->values;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class Fixture
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}

?>
