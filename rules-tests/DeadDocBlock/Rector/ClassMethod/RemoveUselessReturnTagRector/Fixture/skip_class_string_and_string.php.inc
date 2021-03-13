<?php

namespace Rector\Tests\DeadDocBlock\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class SkipClassStringAndString
{
    /**
     * @return class-string
     */
    public function foo(): string
    {

    }
}
