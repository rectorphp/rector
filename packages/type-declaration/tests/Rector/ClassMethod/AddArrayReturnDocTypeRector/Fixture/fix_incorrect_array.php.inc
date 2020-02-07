<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class FixIncorrectArray
{
    /**
     * @return string[]
     */
    public function getValues()
    {
        return [1, 2, 3];
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class FixIncorrectArray
{
    /**
     * @return int[]
     */
    public function getValues()
    {
        return [1, 2, 3];
    }
}

?>
