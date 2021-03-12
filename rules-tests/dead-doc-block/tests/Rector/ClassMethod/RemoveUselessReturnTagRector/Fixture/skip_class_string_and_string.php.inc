<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class SkipClassStringAndString
{
    /**
     * @return class-string
     */
    public function foo(): string
    {

    }
}
