<?php

declare(strict_types=1);

namespace Rector\MockistaToMockery\Tests\Rector\Class_\MockeryTearDownRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MockistaToMockery\Rector\Class_\MockeryTearDownRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MockeryTearDownRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
