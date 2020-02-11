<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\AbstractClassWithAssert;

class KeepWithParentMethodAssert extends AbstractClassWithAssert
{
    public function test()
    {
        $this->doAssertThis();
    }
}
