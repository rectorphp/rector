<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClassParamClassName
{
    /**
     * @param stdClass $a
     */
    function foo(stdClass $a)
    {

    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClassParamClassName
{
    function foo(stdClass $a)
    {

    }
}

?>
