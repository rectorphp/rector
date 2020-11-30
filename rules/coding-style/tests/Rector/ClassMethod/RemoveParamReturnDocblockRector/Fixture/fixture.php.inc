<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClass
{
    /**
     * @param string $a
     * @param string $b
     * @param string $c description
     * @return stdClass
     */
    function foo(string $a, string $b, string $c): stdClass
    {

    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClass
{
    /**
     * @param string $c description
     */
    function foo(string $a, string $b, string $c): stdClass
    {

    }
}

?>
