<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipMarkedSkippedTestStaticFile extends TestCase
{
    public function testMultipleArguments(): void
    {
        static::markTestSkipped('Skip it');

        $this->assertTrue('...');
    }
}
