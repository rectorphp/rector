<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\FixturePhp80;

class SkipPropertyPromotion
{
    public function __construct(private int $age)
    {
    }
}
