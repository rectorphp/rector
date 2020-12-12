<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

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

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class SomeClassParamClassName
{
    function foo(stdClass $a)
    {

    }
}

?>
