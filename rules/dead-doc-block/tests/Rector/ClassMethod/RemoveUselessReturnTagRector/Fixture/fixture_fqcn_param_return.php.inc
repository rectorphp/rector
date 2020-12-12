<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class FixtureFqcnParamReturn
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

class FixtureFqcnParamReturn
{
    function foo(): \stdClass
    {

    }
}

?>
