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

    /**
     * @dataProvider provideDataForTestKeepComments()
     */
    public function testKeepComments(string $file): void
    {
        $this->markTestSkipped('Temporary skip removed docs');
        $this->doTestFile($file);
    }

    public function provideDataForTestKeepComments(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureRemovedComments');
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadStmtRector::class;
    }
}
