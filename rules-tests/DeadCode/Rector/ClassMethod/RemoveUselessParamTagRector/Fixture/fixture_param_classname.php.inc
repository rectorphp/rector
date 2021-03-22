<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

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

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class SomeClassParamClassName
{
    function foo(stdClass $a)
    {

    }
}

?>
