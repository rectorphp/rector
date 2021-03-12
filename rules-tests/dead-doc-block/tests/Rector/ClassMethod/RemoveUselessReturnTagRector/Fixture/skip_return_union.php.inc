<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class SkipReturnUnion
{
    /**
     * @return array|string $a
     */
    function foo($a)
    {

    }
}
