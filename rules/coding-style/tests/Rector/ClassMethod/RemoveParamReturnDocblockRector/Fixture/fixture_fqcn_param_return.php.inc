<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClassFQCNParamReturn
{
    /**
     * @param stdClass $a
     * @return stdClass
     */
    function foo(\stdClass $a): \stdClass
    {

    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClassFQCNParamReturn
{
    function foo(\stdClass $a): \stdClass
    {

    }
}

?>
