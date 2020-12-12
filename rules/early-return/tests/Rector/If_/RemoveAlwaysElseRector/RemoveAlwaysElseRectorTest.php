<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector;

use Iterator;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveAlwaysElseRectorTest extends AbstractRectorTestCase
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
        return RemoveAlwaysElseRector::class;
    }
}
