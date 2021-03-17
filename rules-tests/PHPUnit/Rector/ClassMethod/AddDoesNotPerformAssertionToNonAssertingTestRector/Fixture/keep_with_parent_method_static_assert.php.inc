<?php

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\AbstractClassWithStaticAssert;

class KeepWithParentMethodStaticAssert extends AbstractClassWithStaticAssert
{
    public function test()
    {
        $this->doAssertThis();
    }
}
