<?php

namespace Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector;

class SomeClass
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
