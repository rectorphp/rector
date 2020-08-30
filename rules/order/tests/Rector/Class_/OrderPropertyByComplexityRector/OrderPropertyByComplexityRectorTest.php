<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Order\Rector\Class_\OrderPropertyByComplexityRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderPropertyByComplexityRectorTest extends AbstractRectorTestCase
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
        return OrderPropertyByComplexityRector::class;
    }
}
