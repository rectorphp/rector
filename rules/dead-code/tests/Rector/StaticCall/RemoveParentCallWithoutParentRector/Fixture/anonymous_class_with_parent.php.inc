<?php

namespace Rector\DeadCode\Tests\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class SomeClass
{
    public function foo()
    {
        $class = new class() extends Base
        {
            public function foo()
            {
                parent::foo();
            }
        };
    }
}

class Base
{
    public function foo()
    {

    }
}
