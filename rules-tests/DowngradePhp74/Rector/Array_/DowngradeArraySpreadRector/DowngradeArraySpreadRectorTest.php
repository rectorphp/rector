<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;

use Iterator;
use Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeArraySpreadRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
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
        return DowngradeArraySpreadRector::class;
    }
}
