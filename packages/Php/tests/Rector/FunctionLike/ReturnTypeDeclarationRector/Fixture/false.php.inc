<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class BoolClass
{
    /**
     * @return false
     */
    function someFunction($value)
    {
        return $value;
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
    function someFunction($value): bool
    {
        return $value;
    }
}

?>
