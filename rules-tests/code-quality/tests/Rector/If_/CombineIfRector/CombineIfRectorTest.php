<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\CombineIfRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CombineIfRectorTest extends AbstractRectorTestCase
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
        return CombineIfRector::class;
    }
}
