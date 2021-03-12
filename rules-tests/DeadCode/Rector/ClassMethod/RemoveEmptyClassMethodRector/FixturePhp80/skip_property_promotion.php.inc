<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\FixturePhp80;

final class SkipPropertyPromotion
{
    public function __construct(public int $name)
    {
    }
}
