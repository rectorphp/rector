<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector\FixtureInterface
{
    interface Foo
    {
        public function bar($value1);
    }
}

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture
{
    use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector\FixtureInterface\Foo;

    class Baz
    {
        public function test()
        {
            $class = new class implements Foo
            {
                public function bar($value1)
                {
                }
            };
        }
    }
}
