<?php

declare(strict_types=1);

namespace Rector\NetteUtilsCodeQuality\Tests\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteUtilsCodeQuality\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceTimeNumberWithDateTimeConstantRectorTest extends AbstractRectorTestCase
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
        return ReplaceTimeNumberWithDateTimeConstantRector::class;
    }
}
