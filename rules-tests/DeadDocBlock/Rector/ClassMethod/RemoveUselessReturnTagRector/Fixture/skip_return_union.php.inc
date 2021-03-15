<?php

namespace Rector\Tests\DeadDocBlock\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class SkipReturnUnion
{
    /**
     * @return array|string $a
     */
    function foo($a)
    {

    }
}
