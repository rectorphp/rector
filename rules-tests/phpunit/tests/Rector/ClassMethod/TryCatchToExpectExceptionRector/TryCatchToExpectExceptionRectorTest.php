<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\TryCatchToExpectExceptionRector;

use Iterator;
use Rector\PHPUnit\Rector\ClassMethod\TryCatchToExpectExceptionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TryCatchToExpectExceptionRectorTest extends AbstractRectorTestCase
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
        return TryCatchToExpectExceptionRector::class;
    }
}
