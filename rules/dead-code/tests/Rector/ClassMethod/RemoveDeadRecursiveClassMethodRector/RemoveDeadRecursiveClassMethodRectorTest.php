<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveDeadRecursiveClassMethodRectorTest extends AbstractRectorTestCase
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
        return RemoveDeadRecursiveClassMethodRector::class;
    }
}
