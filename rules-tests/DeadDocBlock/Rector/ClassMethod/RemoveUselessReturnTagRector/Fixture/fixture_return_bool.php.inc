<?php

namespace Rector\Tests\DeadDocBlock\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

class FixtureReturnBool
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

namespace Rector\Tests\DeadDocBlock\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

class FixtureReturnBool
{
    function foo(): bool
    {

    }
}

?>
