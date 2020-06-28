<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture;

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
