<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class SkipStatic
{
    public static $x;

    public function __construct(float $x = 0.0)
    {
        self::$x = $x;
    }
}
