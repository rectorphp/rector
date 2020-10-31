<?php

declare(strict_types=1);

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector;

use Iterator;
use Rector\Performance\Rector\FuncCall\PreslashSimpleFunctionRector;
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

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return PreslashSimpleFunctionRector::class;
    }
}
