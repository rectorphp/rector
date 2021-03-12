<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class AddWithoutReturnTypeDeclaration
{
    /**
     * @var int[]
     */
    private $values;

    public function getValues()
    {
        return $this->values;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class AddWithoutReturnTypeDeclaration
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @return int[]
     */
    public function getValues()
    {
        return $this->values;
    }
}

?>
