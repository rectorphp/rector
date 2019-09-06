<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class TestVoidType
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

class TestVoidType
{
    /**
     * @return false
     */
    function someFunction(): void
    {
    }
}

?>
