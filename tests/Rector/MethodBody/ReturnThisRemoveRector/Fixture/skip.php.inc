<?php

namespace Rector\Core\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture;

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
