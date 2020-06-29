<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\Class_\RemoveUnusedClassesRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveUnusedClassesRectorTest extends AbstractRectorTestCase
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
        return RemoveUnusedClassesRector::class;
    }
}
