<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class SkipAccessedVariableBeforeAssign
{
    public $x;

    function __construct(string $x) {
        $x = 'preprended-' . $x;
        $this->x = $x;
    }
}
