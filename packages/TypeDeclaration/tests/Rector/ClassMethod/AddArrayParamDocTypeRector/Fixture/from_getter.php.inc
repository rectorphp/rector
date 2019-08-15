<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class FromGetter
{
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class FromGetter
{
    private $values;

    /**
     * @param int[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}

?>
