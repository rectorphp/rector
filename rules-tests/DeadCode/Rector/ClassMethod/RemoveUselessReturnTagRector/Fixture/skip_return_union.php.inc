<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class SkipReturnUnion
{
    /**
     * @return array|string $a
     */
    function foo($a)
    {

    }
}
