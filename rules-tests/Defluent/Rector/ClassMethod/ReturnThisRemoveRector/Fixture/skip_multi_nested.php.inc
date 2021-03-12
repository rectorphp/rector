<?php

namespace Rector\Tests\Defluent\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

class SkipMultiNested
{
    public function someFunction()
    {
        if (mt_rand(1, 2)) {
            return $this;
        }

        return $this;
    }
}
