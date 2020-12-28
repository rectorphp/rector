<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

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

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

class FixtureReturnBool
{
    function foo(): bool
    {

    }
}

?>
