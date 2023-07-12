<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\SomeUser;

final class SkipCallAfterMock extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject|SomeUser $mockNetteUser;

    public function test()
    {
        $this->mockNetteUser = $this->getMockBuilder(SomeUser::class)
            ->getMock();

        $this->runSomething();
    }
}
