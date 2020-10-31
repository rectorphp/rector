<?php

declare(strict_types=1);

namespace Rector\Php52\Tests\Rector\Switch_\ContinueToBreakInSwitchRector;

use Iterator;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ContinueToBreakInSwitchRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfoWithoutAutoload($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ContinueToBreakInSwitchRector::class;
    }
}
