<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

final class SkipUnionTypedWithCallableType
{
    /** @var callable|array */
    public $cb;

    public function __construct(callable|array $cb = null)
    {
        $this->cb = $cb;
    }
}
