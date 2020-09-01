<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SimpleArray
{
    public function getValues(): array
    {
        return [];
    }

    /**
     * @return void
     */
    public function getAnEmptyArray(): array
    {
        return [];
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SimpleArray
{
    /**
     * @return mixed[]
     */
    public function getValues(): array
    {
        return [];
    }

    /**
     * @return mixed[]
     */
    public function getAnEmptyArray(): array
    {
        return [];
    }
}

?>
