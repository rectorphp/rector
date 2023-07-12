<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\SomeUser;

final class SkipCallOnMockProperty extends TestCase
{
    private MockObject|SomeUser $mockNetteUser;

    protected function setUp(): void
    {
        $this->mockNetteUser = $this->getMockBuilder(SomeUser::class)
            ->getMock();
    }

    public function test(): void
    {
        $this->runSomething($this->mockNetteUser);
    }
}
