<?php

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

class SomeMultiNested
{
    public function someFunction()
    {
        if (mt_rand(1, 2)) {
            return $this;
        }

        return $this;
    }
}
