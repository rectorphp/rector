<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipMethodCallWithPropertyMock extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $mock;

    protected function setUp(): void
    {
        $this->mock = $this->createMock('SomeClass');
    }

    public function test()
    {
        $this->callMockedProperty();
    }

    private function callMockedProperty()
    {
        $this->mock->expects('something');
    }
}
