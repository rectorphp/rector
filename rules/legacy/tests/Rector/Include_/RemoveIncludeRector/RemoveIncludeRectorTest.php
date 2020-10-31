<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector;

use Iterator;
use Rector\Legacy\Rector\Include_\RemoveIncludeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveIncludeRectorTest extends AbstractRectorTestCase
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
        return RemoveIncludeRector::class;
    }
}
