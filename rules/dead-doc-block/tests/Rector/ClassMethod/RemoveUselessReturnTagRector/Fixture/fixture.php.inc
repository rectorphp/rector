<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class SomeClass
{
    /**
     * @return stdClass
     */
    function foo(): stdClass
    {

    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class SomeClass
{
    function foo(): stdClass
    {

    }
}

?>
