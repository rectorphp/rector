<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\AbstractClassWithStaticAssert;

class KeepWithParentMethodStaticAssert extends AbstractClassWithStaticAssert
{
    public function test()
    {
        $this->doAssertThis();
    }
}
