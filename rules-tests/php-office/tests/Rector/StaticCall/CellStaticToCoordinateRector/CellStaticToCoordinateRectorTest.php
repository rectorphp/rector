<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Tests\Rector\StaticCall\CellStaticToCoordinateRector;

use Iterator;
use Rector\PHPOffice\Rector\StaticCall\CellStaticToCoordinateRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CellStaticToCoordinateRectorTest extends AbstractRectorTestCase
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
        return CellStaticToCoordinateRector::class;
    }
}
