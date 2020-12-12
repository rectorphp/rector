<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class FixtureFqcnDocblock
{
    /**
     * @param \stdClass $a
     */
    function foo(stdClass $a)
    {

    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class FixtureFqcnDocblock
{
    function foo(stdClass $a)
    {

    }
}

?>
