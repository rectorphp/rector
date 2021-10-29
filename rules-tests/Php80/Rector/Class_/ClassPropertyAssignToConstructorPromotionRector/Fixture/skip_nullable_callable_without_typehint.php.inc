<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

final class SkipNullableCallableWithoutTypehint
{
    /** @var callable|null */
    public $cb;

    public function __construct($cb = null)
    {
        $this->cb = $cb;
    }
}
