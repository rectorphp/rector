<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class SkipPrivate
{
    private function __construct()
    {
    }
}
