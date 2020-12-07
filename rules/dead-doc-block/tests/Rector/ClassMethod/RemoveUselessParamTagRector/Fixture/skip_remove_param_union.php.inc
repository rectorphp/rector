<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

class SomeClassParamUnion
{
    /**
     * @param array|string $a
     */
    function foo($a)
    {

    }
}

?>
