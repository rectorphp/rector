<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\Class_\RemoveFinalFromEntityRector;

use Iterator;
use Rector\Restoration\Rector\Class_\RemoveFinalFromEntityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveFinalFromEntityRectorTest extends AbstractRectorTestCase
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
        return RemoveFinalFromEntityRector::class;
    }
}
