<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Expression\RemoveDeadStmtRector;

use Iterator;
use Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveDeadStmtRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @dataProvider provideDataForTestKeepComments()
     */
    public function testKeepComments(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
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
