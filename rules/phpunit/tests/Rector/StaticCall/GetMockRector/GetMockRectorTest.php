<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\StaticCall\GetMockRector;

use Iterator;
use Rector\PHPUnit\Rector\StaticCall\GetMockRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class GetMockRectorTest extends AbstractRectorTestCase
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
        return GetMockRector::class;
    }
}
