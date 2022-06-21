<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class SkipPropertyPromotion
{
    public function __construct(private int $age)
    {
    }
}
