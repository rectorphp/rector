<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class BoolClass
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

class BoolClass
{
    /**
     * @return false
     */
    function someFunction(): bool
    {
    }
}

?>
