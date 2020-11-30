<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

class SomeClassReturnBool
{
    /**
     * @param string $a
     * @param string $b
     * @param string $c description
     * @return bool
     */
    function foo(string $a, string $b, string $c): bool
    {

    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

class SomeClassReturnBool
{
    /**
     * @param string $c description
     */
    function foo(string $a, string $b, string $c): bool
    {

    }
}

?>
