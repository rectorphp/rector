<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ArrayDimFetchControlToGetComponentMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteCodeQuality\Rector\ArrayDimFetch\ArrayDimFetchControlToGetComponentMethodCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArrayDimFetchControlToGetComponentMethodCallRectorTest extends AbstractRectorTestCase
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
        return ArrayDimFetchControlToGetComponentMethodCallRector::class;
    }
}
