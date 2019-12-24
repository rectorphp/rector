<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

abstract class KeepProtectedConstructorOnAbstractClass
{
    protected function __construct()
    {

    }
}
