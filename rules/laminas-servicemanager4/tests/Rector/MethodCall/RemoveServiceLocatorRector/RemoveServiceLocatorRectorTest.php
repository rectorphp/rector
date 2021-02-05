<?php

declare(strict_types=1);

namespace Rector\LaminasServiceManager4\Tests\Rector\MethodCall\RemoveServiceLocatorRector;

use Iterator;
use Rector\LaminasServiceManager4\Rector\MethodCall\RemoveServiceLocatorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveServiceLocatorRectorTest extends AbstractRectorTestCase
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
        return RemoveServiceLocatorRector::class;
    }
}
