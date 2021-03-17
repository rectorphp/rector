<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class SkipNonConstructor
{
    public $x;

    public function nonConstructor(float $x = 0.0)
    {
        $this->x = $x;
    }
}
