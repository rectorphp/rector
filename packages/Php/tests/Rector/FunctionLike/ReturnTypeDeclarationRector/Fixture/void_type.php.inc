<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

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

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

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
