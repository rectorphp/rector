<?php

declare(strict_types=1);

namespace Rector\Tests\MockeryToProphecy\Rector\ClassMethod\MockeryToProphecyRector;

use Iterator;
use Rector\MockeryToProphecy\Rector\ClassMethod\MockeryCreateMockToProphizeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MockeryToProphecyRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MockeryCreateMockToProphizeRector::class;
    }
}
