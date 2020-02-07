<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

class KeepAssert extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $nothing = 5;
        $this->assertNotNull(5);
    }

    public function testStatic()
    {
        $nothing = 5;
        self::assertNotNull(5);
    }
}
