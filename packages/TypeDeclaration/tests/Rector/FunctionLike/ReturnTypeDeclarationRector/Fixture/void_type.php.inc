<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class VoidType
{
    /**
     * @return false
     */
    function someFunction()
    {
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class VoidType
{
    /**
     * @return false
     */
    function someFunction(): void
    {
    }
}

?>
