<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class SkipStatic
{
    public static $x;

    public function __construct(float $x = 0.0)
    {
        self::$x = $x;
    }
}
