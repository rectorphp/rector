<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

final class SkipEarlyReturn
{
    public int $x = 60;

    public function __construct(int $x)
    {
        if ($x < 0) {
            return;
        }

        $this->x = $x;
    }
}
