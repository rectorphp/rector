<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipMarkedSkippedTestFile extends TestCase
{
    public function testMultipleArguments(): void
    {
        $this->markTestSkipped('Conflicting with previous test() for unknown reason. Works well separately');

        $this->assertTrue('...');
        $this->assertFalse('...');
        $this->assertSame('...', '...');
    }
}
