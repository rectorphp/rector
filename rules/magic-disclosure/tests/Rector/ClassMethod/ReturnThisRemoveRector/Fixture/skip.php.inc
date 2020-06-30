<?php

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

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
