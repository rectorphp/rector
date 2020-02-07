<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

abstract class AnAbstractClass
{
    public abstract function foo(string $foo, string $bar);

}

class AChildClass extends AnAbstractClass
{
    public function foo(string $foo, string $bar)
    {
        return $foo;
    }
}


class AChildClass2 extends AnAbstractClass
{
    public function foo(string $foo, string $bar)
    {
        return $bar;
    }
}
