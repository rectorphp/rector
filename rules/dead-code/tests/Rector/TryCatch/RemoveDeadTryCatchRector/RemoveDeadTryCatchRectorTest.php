<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveDeadTryCatchRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return RemoveDeadTryCatchRector::class;
    }
}
