<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\FixturePhp80;

final class SkipPropertyPromotion
{
    public function __construct(public int $name)
    {
    }
}
