<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class SomeClassFQCNParamReturn
{
    /**
     * @param stdClass $a
     */
    function foo(\stdClass $a)
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class SomeClassFQCNParamReturn
{
    function foo(\stdClass $a)
    {

    }
}

?>
