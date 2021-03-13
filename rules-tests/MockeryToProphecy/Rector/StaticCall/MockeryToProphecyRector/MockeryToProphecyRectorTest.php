<?php

declare(strict_types=1);

namespace Rector\Tests\MockeryToProphecy\Rector\StaticCall\MockeryToProphecyRector;

use Iterator;
use Rector\MockeryToProphecy\Rector\StaticCall\MockeryCloseRemoveRector;
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
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MockeryCloseRemoveRector::class;
    }
}
