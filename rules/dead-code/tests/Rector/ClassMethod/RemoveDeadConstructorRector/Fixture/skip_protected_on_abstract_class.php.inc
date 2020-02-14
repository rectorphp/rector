<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

abstract class SkipProtectedOnAbstractClass
{
    protected function __construct()
    {
    }
}
