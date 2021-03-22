<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

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

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

class RemoveReturnNullable
{
    function foo(): ?stdClass
    {

    }
}

?>
