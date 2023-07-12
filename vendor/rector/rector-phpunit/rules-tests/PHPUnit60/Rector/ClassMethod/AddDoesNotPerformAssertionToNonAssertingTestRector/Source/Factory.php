<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source;

class Factory
{
    public function create() : \stdClass
    {
        return new \stdClass();
    }
}
