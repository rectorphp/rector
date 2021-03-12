<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class SkipNonConstructor
{
    public $x;

    public function nonConstructor(float $x = 0.0)
    {
        $this->x = $x;
    }
}
