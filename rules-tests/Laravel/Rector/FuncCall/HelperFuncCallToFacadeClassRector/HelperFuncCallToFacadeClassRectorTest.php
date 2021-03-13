<?php

declare(strict_types=1);

namespace Rector\Tests\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector;

use Iterator;
use Rector\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class HelperFuncCallToFacadeClassRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return HelperFuncCallToFacadeClassRector::class;
    }
}
