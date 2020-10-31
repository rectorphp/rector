<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\Class_\OrderClassConstantsByIntegerValueRector;

use Iterator;
use Rector\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderClassConstantsByIntegerValueRectorTest extends AbstractRectorTestCase
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
        return OrderClassConstantsByIntegerValueRector::class;
    }
}
