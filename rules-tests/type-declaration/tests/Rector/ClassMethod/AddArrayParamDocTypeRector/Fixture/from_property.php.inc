<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class FromProperty
{
    /**
     * @var int[]
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class FromProperty
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @param int[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
}

?>
