<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipMarkedIncompleteTestFile extends TestCase
{
    public function testMultipleArguments(): void
    {
        $this->markTestIncomplete('need to be completed');

        $db = db_connect();
        $db->run();
    }
}
