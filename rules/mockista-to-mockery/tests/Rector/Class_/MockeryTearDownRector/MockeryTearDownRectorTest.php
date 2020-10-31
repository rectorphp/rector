<?php

declare(strict_types=1);

namespace Rector\MockistaToMockery\Tests\Rector\Class_\MockeryTearDownRector;

use Iterator;
use Rector\MockistaToMockery\Rector\Class_\MockeryTearDownRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MockeryTearDownRectorTest extends AbstractRectorTestCase
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
        return MockeryTearDownRector::class;
    }
}
