<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

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
