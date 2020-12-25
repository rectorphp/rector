<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector;

use Iterator;
use Rector\Privatization\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MakeUnusedClassesWithChildrenAbstractRectorTest extends AbstractRectorTestCase
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
        return MakeUnusedClassesWithChildrenAbstractRector::class;
    }
}
