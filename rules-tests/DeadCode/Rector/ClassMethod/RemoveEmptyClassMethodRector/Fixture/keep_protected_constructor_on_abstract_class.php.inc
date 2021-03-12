<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

abstract class KeepProtectedConstructorOnAbstractClass
{
    protected function __construct()
    {

    }
}
