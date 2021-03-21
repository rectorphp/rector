<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class SkipClassStringAndString
{
    /**
     * @return class-string
     */
    public function foo(): string
    {

    }
}
