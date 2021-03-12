<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class SkipProtectedOnNonFinalClass
{
    protected function __construct()
    {
    }
}
