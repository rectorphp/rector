<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;

use Iterator;
use Rector\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class LocallyCalledStaticMethodToNonStaticRectorTest extends AbstractRectorTestCase
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
        return LocallyCalledStaticMethodToNonStaticRector::class;
    }
}
