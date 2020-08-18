<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Class_\ClassConstantToSelfClassRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\Class_\ClassConstantToSelfClassRector;
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
