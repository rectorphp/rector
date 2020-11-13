<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClassFQCNDocblock
{
    /**
     * @param \stdClass $a
     * @return \stdClass
     */
    function foo(stdClass $a): stdClass
    {

    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClassFQCNDocblock
{
    function foo(stdClass $a): stdClass
    {

    }
}

?>
