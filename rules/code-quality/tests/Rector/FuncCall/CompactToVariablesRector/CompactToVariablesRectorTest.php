<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector;

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

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return CompactToVariablesRector::class;
    }
}
