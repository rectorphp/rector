<?php

namespace Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector;

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
