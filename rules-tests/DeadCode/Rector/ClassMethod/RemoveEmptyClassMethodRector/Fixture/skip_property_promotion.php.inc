<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

final class SkipPropertyPromotion
{
    public function __construct(public int $name)
    {
    }
}
