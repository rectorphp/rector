<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector;

use Iterator;
use Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SplitGroupedConstantsAndPropertiesRectorTest extends AbstractRectorTestCase
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
        return SplitGroupedConstantsAndPropertiesRector::class;
    }
}
