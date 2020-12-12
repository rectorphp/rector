<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class FixtureParamClassname
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

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class FixtureParamClassname
{
    function foo(stdClass $a)
    {

    }
}

?>
