<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector;

use Iterator;
use Rector\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveEmptyMethodCallRectorTest extends AbstractRectorTestCase
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
        return RemoveEmptyMethodCallRector::class;
    }
}
