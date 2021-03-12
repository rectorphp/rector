<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\FuncCall\ClassOnObjectRector;

use Iterator;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClassOnObjectRectorTest extends AbstractRectorTestCase
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
        return ClassOnObjectRector::class;
    }
}
