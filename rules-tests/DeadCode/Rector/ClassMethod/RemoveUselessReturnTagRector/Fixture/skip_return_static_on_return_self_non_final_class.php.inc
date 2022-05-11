<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

class SkipReturnStaticOnReturnSelfNonFinalClass
{
    private static self $property;

    /**
     * @return static
     */
    public static function resolve(): self
    {
        return self::$property ??= new static();
    }
}
