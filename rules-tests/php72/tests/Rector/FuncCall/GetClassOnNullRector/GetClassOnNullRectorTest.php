<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\FuncCall\GetClassOnNullRector;

use Iterator;
use Rector\Php72\Rector\FuncCall\GetClassOnNullRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class GetClassOnNullRectorTest extends AbstractRectorTestCase
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
        return GetClassOnNullRector::class;
    }
}
