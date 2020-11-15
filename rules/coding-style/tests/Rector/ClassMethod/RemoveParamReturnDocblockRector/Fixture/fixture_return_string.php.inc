<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

class SomeClassReturnString
{
    /**
     * @param string $a
     * @param string $b
     * @param string $c description
     * @return string
     */
    function foo(string $a, string $b, string $c): string
    {

    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

class SomeClassReturnString
{
    /**
     * @param string $c description
     */
    function foo(string $a, string $b, string $c): string
    {

    }
}

?>
