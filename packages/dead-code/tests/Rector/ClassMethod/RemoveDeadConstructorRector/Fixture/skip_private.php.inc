<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class SkipPrivate
{
    private function __construct()
    {
    }
}
