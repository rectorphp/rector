<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PreslashSimpleFunctionRectorTest extends AbstractRectorTestCase
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
        return PreslashSimpleFunctionRector::class;
    }
}
