<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\Class_\OrderPropertiesByVisibilityRector;

use Iterator;
use Rector\Order\Rector\Class_\OrderPropertiesByVisibilityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderPropertiesByVisibilityRectorTest extends AbstractRectorTestCase
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
        return OrderPropertiesByVisibilityRector::class;
    }
}
