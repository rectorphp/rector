<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

trait SkipReturnStaticOnReturnTrait
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
