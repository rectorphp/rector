<?php

declare(strict_types=1);

namespace Rector\Tests\PHPOffice\Rector\StaticCall\ChangeChartRendererRector;

use Iterator;
use Rector\PHPOffice\Rector\StaticCall\ChangeChartRendererRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeChartRendererRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
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
        return ChangeChartRendererRector::class;
    }
}
