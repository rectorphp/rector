<?php

namespace Rector\Tests\DeadDocBlock\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

class SkipRemoveParamUnion
{
    /**
     * @param array|string $a
     */
    function foo($a)
    {

    }
}

?>
