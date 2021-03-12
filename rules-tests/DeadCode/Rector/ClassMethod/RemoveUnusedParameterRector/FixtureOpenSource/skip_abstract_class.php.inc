<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\FixtureOpenSource;

abstract class SkipAbstractClass
{
    // free abstract method
    public function foo(string $foo, string $bar)
    {

    }
}

class UsingAbstractClass extends SkipAbstractClass
{
}
