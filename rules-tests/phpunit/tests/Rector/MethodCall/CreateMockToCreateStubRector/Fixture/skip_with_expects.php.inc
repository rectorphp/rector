<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\CreateMockToCreateStubRector\Fixture;

use PHPUnit\Framework\TestCase;

class SkipWithExpects extends TestCase
{
    public function testItBehavesAsExpected(): void
    {
        $mock = $this->createMock(\Exception::class);
        $mock->expects($this->once())
            ->method('getMessage')
            ->willReturn('a message');
    }
}
