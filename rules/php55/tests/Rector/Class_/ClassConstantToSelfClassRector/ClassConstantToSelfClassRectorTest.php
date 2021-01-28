<?php

declare(strict_types=1);

namespace Rector\Php55\Tests\Rector\Class_\ClassConstantToSelfClassRector;

use Iterator;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClassConstantToSelfClassRectorTest extends AbstractRectorTestCase
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
        return ClassConstantToSelfClassRector::class;
    }
}
