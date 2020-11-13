<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClassReturnNullable
{
    /**
     * @param string $a
     * @param string $b
     * @param string $c description
     * @return stdClass|null
     */
    function foo(string $a, string $b, string $c): ?stdClass
    {

    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClassReturnNullable
{
    /**
     * @param string $c description
     * @return stdClass|null
     */
    function foo(string $a, string $b, string $c): ?stdClass
    {

    }
}

?>
