<?php

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\FixtureConfiguration;

class SomeClassNotInTheList
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
