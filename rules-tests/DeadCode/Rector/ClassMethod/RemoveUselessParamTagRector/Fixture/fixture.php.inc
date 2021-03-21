<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

class Fixture
{
    /**
     * @param string $a
     * @param string $b
     * @param string $c description
     */
    function foo(string $a, string $b, string $c)
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

class Fixture
{
    /**
     * @param string $c description
     */
    function foo(string $a, string $b, string $c)
    {

    }
}

?>
