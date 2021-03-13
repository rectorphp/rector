<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CompactToVariablesRectorTest extends AbstractRectorTestCase
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
        return CompactToVariablesRector::class;
    }
}
