<?php

namespace Rector\Tests\DeadDocBlock\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class RemoveReturnNullable
{
    /**
     * @return stdClass|null
     */
    function foo(): ?stdClass
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\DeadDocBlock\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class RemoveReturnNullable
{
    function foo(): ?stdClass
    {

    }
}

?>
