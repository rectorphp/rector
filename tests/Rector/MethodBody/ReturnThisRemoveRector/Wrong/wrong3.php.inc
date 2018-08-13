<?php

namespace Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector;

class SomeClassWithReturnAnnotations
{
    /**
     * @return SomeClassWithReturnAnnotations
     */
    public function someFunction()
    {
        return $this;
    }
}
