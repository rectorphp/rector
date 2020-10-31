<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentImplodeRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ConsistentImplodeRectorTest extends AbstractRectorTestCase
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
        return ConsistentImplodeRector::class;
    }
}
