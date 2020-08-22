<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\Class_\OrderConstantsByVisibilityRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Order\Rector\Class_\OrderConstantsByVisibilityRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderConstantsByVisibilityRectorTest extends AbstractRectorTestCase
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
        return OrderConstantsByVisibilityRector::class;
    }
}
