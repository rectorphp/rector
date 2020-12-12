<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class Fixture
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

class Fixture
{
    function foo(): stdClass
    {

    }
}

?>
