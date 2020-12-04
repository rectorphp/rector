<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\FuncCall\StringifyDefineRector;

use Iterator;
use Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP < 8.0
 */
final class StringifyDefineRectorTest extends AbstractRectorTestCase
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
        return StringifyDefineRector::class;
    }
}
