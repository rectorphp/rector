<?php

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\AbstractClassWithAssert;

final class KeepWithParentMethodAssert extends AbstractClassWithAssert
{
    public function test()
    {
        $this->doAssertThis();
    }
}
