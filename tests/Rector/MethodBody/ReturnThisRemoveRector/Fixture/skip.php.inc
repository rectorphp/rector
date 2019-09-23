<?php

namespace Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture;

class SomeAnotherClass
{
    public function someFunction()
    {
        foo();
        return $this;
    }

    public function otherFunction()
    {
        bar();
        return $this;
    }
}
