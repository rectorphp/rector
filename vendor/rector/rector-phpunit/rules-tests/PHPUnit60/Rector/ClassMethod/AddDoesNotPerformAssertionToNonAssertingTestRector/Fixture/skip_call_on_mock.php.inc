<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\SomeUser;

final class SkipCallOnMock extends TestCase
{
    public function test()
    {
        $mockNetteUser = $this->getMockBuilder(SomeUser::class)
            ->getMock();

        $this->runSomething($mockNetteUser);
    }
}
