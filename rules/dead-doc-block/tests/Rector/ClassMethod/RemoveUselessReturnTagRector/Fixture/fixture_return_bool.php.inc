<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

class SomeClassReturnBool
{
    /**
     * @return bool
     */
    function foo(): bool
    {

    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

class SomeClassReturnBool
{
    function foo(): bool
    {

    }
}

?>
