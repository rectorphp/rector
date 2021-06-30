<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

final class SkipNullableCallable
{
    /** @var callable|null */
    public $cb;

    public function __construct(?callable $cb = null)
    {
        $this->cb = $cb;
    }
}
