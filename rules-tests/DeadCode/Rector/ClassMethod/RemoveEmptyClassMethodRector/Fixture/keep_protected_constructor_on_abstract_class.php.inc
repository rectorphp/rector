<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

abstract class KeepProtectedConstructorOnAbstractClass
{
    protected function __construct()
    {

    }
}
