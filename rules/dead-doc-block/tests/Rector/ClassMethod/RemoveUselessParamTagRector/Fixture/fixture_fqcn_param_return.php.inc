<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

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

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use stdClass;

class SomeClassFQCNParamReturn
{
    function foo(\stdClass $a)
    {

    }
}

?>
