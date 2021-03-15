<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

abstract class SkipProtectedOnAbstractClass
{
    protected function __construct()
    {
    }
}
