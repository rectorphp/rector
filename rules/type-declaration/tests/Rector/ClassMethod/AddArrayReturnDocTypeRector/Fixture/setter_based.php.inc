<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use stdClass;

class GetterBased
{
    private $values;

    /**
     * @param stdClass[] $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use stdClass;

class GetterBased
{
    private $values;

    /**
     * @param stdClass[] $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return \stdClass[]|null
     */
    public function getValues(): array
    {
        return $this->values;
    }
}

?>
