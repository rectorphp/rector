<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector;

use Iterator;
use Rector\Php73\Rector\FuncCall\RemoveMissingCompactVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveMissingCompactVariableRectorTest extends AbstractRectorTestCase
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
        return RemoveMissingCompactVariableRector::class;
    }
}
