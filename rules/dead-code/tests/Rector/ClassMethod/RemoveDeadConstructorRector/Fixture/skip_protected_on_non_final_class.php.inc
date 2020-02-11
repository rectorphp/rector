<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class SkipProtectedOnNonFinalClass
{
    protected function __construct()
    {
    }
}
