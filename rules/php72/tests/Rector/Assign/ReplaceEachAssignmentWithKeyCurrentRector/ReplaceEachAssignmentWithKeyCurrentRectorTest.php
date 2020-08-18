<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceEachAssignmentWithKeyCurrentRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ReplaceEachAssignmentWithKeyCurrentRector::class;
    }
}
