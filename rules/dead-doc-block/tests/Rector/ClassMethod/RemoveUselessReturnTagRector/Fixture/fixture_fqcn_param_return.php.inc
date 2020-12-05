<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class SomeClassFQCNParamReturn
{
    /**
     * @return stdClass
     */
    function foo(): \stdClass
    {

    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class SomeClassFQCNParamReturn
{
    function foo(): \stdClass
    {

    }
}

?>
