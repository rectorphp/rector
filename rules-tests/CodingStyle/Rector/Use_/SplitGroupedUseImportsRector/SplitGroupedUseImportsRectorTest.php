<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector;

use Iterator;
use Rector\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SplitGroupedUseImportsRectorTest extends AbstractRectorTestCase
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
        return SplitGroupedUseImportsRector::class;
    }
}
