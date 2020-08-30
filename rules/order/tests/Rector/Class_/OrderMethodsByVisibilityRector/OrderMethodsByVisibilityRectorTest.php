<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\Class_\OrderMethodsByVisibilityRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Order\Rector\Class_\OrderMethodsByVisibilityRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderMethodsByVisibilityRectorTest extends AbstractRectorTestCase
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
        return OrderMethodsByVisibilityRector::class;
    }
}
