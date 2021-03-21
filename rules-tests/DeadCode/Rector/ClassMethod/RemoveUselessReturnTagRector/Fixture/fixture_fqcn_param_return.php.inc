<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

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

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class SomeClassFQCNParamReturn
{
    function foo(): \stdClass
    {

    }
}

?>
