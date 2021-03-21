<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class SomeClassFQCNDocblock
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

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class SomeClassFQCNDocblock
{
    function foo(stdClass $a)
    {

    }
}

?>
