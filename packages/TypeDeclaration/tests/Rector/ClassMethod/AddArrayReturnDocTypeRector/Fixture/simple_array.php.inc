<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SimpleArray
{
    public function getValues(): array
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
}

?>
