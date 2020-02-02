<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector;

use Iterator;
use Rector\DeadCode\Rector\Stmt\RemoveDeadStmtRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadStmtRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadStmtRector::class;
    }
}
