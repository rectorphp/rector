<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

abstract class SkipAbstract
{
    public abstract function foo(string $foo, string $bar);
}

class UsingAbstract extends SkipAbstract
{
    public function foo(string $foo, string $bar)
    {
        return $foo;
    }
}
