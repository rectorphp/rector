<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

abstract class KeepProtectedConstructor
{
    protected function __construct()
    {

    }
}
