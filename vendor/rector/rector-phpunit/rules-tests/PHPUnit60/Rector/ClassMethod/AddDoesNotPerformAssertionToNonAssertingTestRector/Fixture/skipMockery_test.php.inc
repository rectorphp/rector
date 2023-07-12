<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use Mockery;
use PHPUnit\Framework\TestCase;

final class SkipMockeryTest extends TestCase
{
    public function test()
    {
        $mockNetteUser = Mockery::mock(User::class);
        $mockNetteUser->expects()->isAllowed('yes', 'vote');
    }
}
